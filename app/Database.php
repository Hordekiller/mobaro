<?php

class Database
{
    private static string $dir = '';

    private static function dir(): string
    {
        if (!self::$dir) {
            self::$dir = __DIR__ . '/../storage/data';
            if (!is_dir(self::$dir)) {
                mkdir(self::$dir, 0755, true);
            }
        }
        return self::$dir;
    }

    private static function file(string $table): string
    {
        return self::dir() . '/' . $table . '.json';
    }

    private static function load(string $table): array
    {
        $file = self::file($table);
        if (!file_exists($file)) {
            return [];
        }
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    private static function save(string $table, array $rows): void
    {
        $dir = self::dir();
        file_put_contents(self::file($table), json_encode(array_values($rows), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private static function nextId(string $table): int
    {
        $rows = self::load($table);
        $max = 0;
        foreach ($rows as $row) {
            if (isset($row['id']) && $row['id'] > $max) {
                $max = (int) $row['id'];
            }
        }
        return $max + 1;
    }

    public static function connection(): ?PDO
    {
        return null;
    }

    public static function insert(string $table, array $data): int
    {
        $rows = self::load($table);
        $id = self::nextId($table);
        $data['id'] = $id;
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $rows[] = $data;
        self::save($table, $rows);
        return $id;
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $rows = self::load($table);
        $count = 0;
        foreach ($rows as &$row) {
            if (self::matchWhere($row, $where, $whereParams)) {
                foreach ($data as $key => $value) {
                    $row[$key] = $value;
                }
                $count++;
            }
        }
        self::save($table, $rows);
        return $count;
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $rows = self::load($table);
        $remaining = [];
        $count = 0;
        foreach ($rows as $row) {
            if (self::matchWhere($row, $where, $params)) {
                $count++;
            } else {
                $remaining[] = $row;
            }
        }
        self::save($table, $remaining);
        return $count;
    }

    public static function query(string $sql, array $params = []): array
    {
        $sql = trim($sql);

        if (preg_match('/^SHOW COLUMNS FROM\s+(.+)/i', $sql, $m)) {
            return self::handleShowColumns($m[1]);
        }

        if (preg_match('/^UPDATE\s+(.+)\s+SET\s+(.+)\s+WHERE\s+(.+)/is', $sql, $m)) {
            return self::handleExpressionUpdate($m[1], $m[2], $m[3], $params);
        }

        if (preg_match('/^SELECT/i', $sql)) {
            if (preg_match('/UNION\s+ALL/i', $sql)) {
                return self::handleUnionAll($sql, $params);
            }
            return self::executeSimpleSelect($sql, $params);
        }

        throw new RuntimeException("Unsupported SQL: " . substr($sql, 0, 100));
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $results = self::query($sql, $params);
        return $results[0] ?? null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params);
    }

    private static function executeSimpleSelect(string $sql, array $params): array
    {
        $parsed = self::parseSelectClause($sql);
        if (!$parsed) {
            return [];
        }

        $table = $parsed['from'];
        $rows = self::load($table);

        if ($parsed['joins']) {
            foreach ($parsed['joins'] as $join) {
                $rows = self::applyJoin($rows, $join);
            }
        }

        if ($parsed['where']) {
            $rows = array_values(array_filter($rows, function ($row) use ($parsed, $params) {
                return self::evaluateWhere($row, $parsed['where'], $parsed['whereParams'] ?? $params);
            }));
        }

        if ($parsed['groupBy']) {
            $rows = self::applyGroupBy($rows, $parsed);
        }

        if ($parsed['orderBy']) {
            self::applyOrderBy($rows, $parsed['orderBy']);
        }

        if ($parsed['limit'] !== null) {
            $offset = $parsed['offset'] ?? 0;
            $rows = array_slice($rows, $offset, $parsed['limit']);
        }

        $rows = self::applyColumns($rows, $parsed['columns'], $table);

        return $rows;
    }

    private static function parseSelectClause(string $sql): ?array
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        if (!preg_match('/^SELECT\s+(.*?)\s+FROM\s+(\S+)(.*)$/is', $sql, $m)) {
            return null;
        }

        $columns = trim($m[1]);
        $from = trim($m[2]);
        $rest = $m[3];

        $joins = [];
        $where = '';
        $groupBy = '';
        $orderBy = '';
        $limit = null;
        $offset = 0;
        $whereParams = [];

        if (preg_match('/\s+LEFT\s+JOIN\s+(\S+)\s+(?:AS\s+)?(\S+)\s+ON\s+(.+?)(?:\s+(LEFT\s+JOIN|WHERE|GROUP|ORDER|LIMIT|UNION|$))/is', $rest, $jm)) {
            $joins[] = [
                'type' => 'LEFT',
                'table' => $jm[1],
                'alias' => $jm[2],
                'on' => trim($jm[3]),
            ];
            if (isset($jm[4])) {
                $rest = $jm[4] . substr($rest, strpos($rest, $jm[4]) + strlen($jm[4]));
            } else {
                $rest = '';
            }
        }

        if (preg_match('/\s+JOIN\s+(\S+)\s+(?:AS\s+)?(\S+)\s+ON\s+(.+?)(?:\s+(LEFT\s+JOIN|JOIN|WHERE|GROUP|ORDER|LIMIT|UNION|$))/is', $rest, $jm)) {
            $joins[] = [
                'type' => 'INNER',
                'table' => $jm[1],
                'alias' => $jm[2],
                'on' => trim($jm[3]),
            ];
            if (isset($jm[4])) {
                $rest = $jm[4] . substr($rest, strpos($rest, $jm[4]) + strlen($jm[4]));
            } else {
                $rest = '';
            }
        }

        if (preg_match('/WHERE\s+(.+?)(?:\s+(GROUP|ORDER|LIMIT|UNION)\s|$)/is', $rest, $wm)) {
            $where = trim($wm[1]);
        }

        if (preg_match('/GROUP\s+BY\s+(.+?)(?:\s+(ORDER|LIMIT|UNION)\s|$)/is', $rest, $gm)) {
            $groupBy = trim($gm[1]);
        }

        if (preg_match('/ORDER\s+BY\s+(.+?)(?:\s+(LIMIT|UNION)\s|$)/is', $rest, $om)) {
            $orderBy = trim($om[1]);
        }

        if (preg_match('/LIMIT\s+(\d+)(?:\s+OFFSET\s+(\d+))?/is', $rest, $lm)) {
            $limit = (int) $lm[1];
            $offset = isset($lm[2]) ? (int) $lm[2] : 0;
        }

        return [
            'columns' => $columns,
            'from' => $from,
            'joins' => $joins,
            'where' => $where,
            'groupBy' => $groupBy,
            'orderBy' => $orderBy,
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    private static function parseSelect(string $sql, array $params): ?array
    {
        $upper = strtoupper(trim($sql));

        if (str_starts_with($upper, 'SELECT')) {
            if (preg_match('/UNION\s+ALL/is', $sql)) {
                return self::handleUnionAll($sql, $params);
            }
            if (preg_match('/SHOW\s+COLUMNS/i', $sql)) {
                return [];
            }
            return null;
        }

        return null;
    }

    private static function handleUnionAll(string $sql, array $params): array
    {
        $parts = preg_split('/UNION\s+ALL/i', $sql);
        $allRows = [];
        $paramIdx = 0;

        foreach ($parts as $part) {
            $partParams = [];
            $part = trim($part);

            preg_match_all('/\?/', $part, $qm);
            $qCount = count($qm[0]);

            if ($qCount > 0) {
                $partParams = array_slice($params, $paramIdx, $qCount);
                $paramIdx += $qCount;
            }

            $rows = self::executeSimpleSelect($part, $partParams);
            $allRows = array_merge($allRows, $rows);
        }

        if (preg_match('/ORDER\s+BY\s+(.+?)(?:\s+LIMIT|\s*$)/is', $sql, $om)) {
            self::applyOrderBy($allRows, trim($om[1]));
        }

        if (preg_match('/LIMIT\s+(\d+)/is', $sql, $lm)) {
            $allRows = array_slice($allRows, 0, (int) $lm[1]);
        }

        return $allRows;
    }

    private static function handleShowColumns(string $table): array
    {
        $rows = self::load($table);
        if (empty($rows)) {
            $schemaFile = __DIR__ . '/../storage/schema/' . $table . '.json';
            if (file_exists($schemaFile)) {
                $schema = json_decode(file_get_contents($schemaFile), true);
                if ($schema) {
                    return $schema;
                }
            }
            return [['Field' => 'id', 'Type' => 'int']];
        }
        $columns = [];
        foreach (array_keys($rows[0]) as $col) {
            $columns[] = ['Field' => $col, 'Type' => 'text'];
        }
        return $columns;
    }

    private static function handleExpressionUpdate(string $table, string $setClause, string $where, array $params): array
    {
        $rows = self::load($table);
        $count = 0;

        if (preg_match('/(\w+)\s*=\s*(\w+)\s*\+\s*\?/i', $setClause, $sm)) {
            $col = $sm[1];
            $addValue = $params[0] ?? 0;

            foreach ($rows as &$row) {
                if (self::matchWhere($row, $where, array_slice($params, 1))) {
                    $row[$col] = ($row[$col] ?? 0) + $addValue;
                    $count++;
                }
            }
            self::save($table, $rows);
        }

        return ['rowCount' => $count];
    }

    private static function matchWhere(array $row, string $where, array $params): bool
    {
        if (empty($where)) {
            return true;
        }

        $paramIdx = 0;
        $conditions = preg_split('/\s+AND\s+/i', $where);
        $namedParams = [];

        foreach ($params as $key => $val) {
            if (is_string($key) && $key !== '') {
                $namedParams[$key] = $val;
            }
        }

        foreach ($conditions as $cond) {
            $cond = trim($cond);

            $condResolved = preg_replace_callback('/:(\w+)/', function ($m) use ($namedParams, &$condValue) {
                return "'" . addslashes((string)($namedParams[$m[1]] ?? '')) . "'";
            }, $cond);

            $condResolved = preg_replace_callback('/\?/', function ($m) use ($params, &$paramIdx) {
                $val = $params[$paramIdx] ?? '';
                $paramIdx++;
                return is_string($val) ? "'" . addslashes($val) . "'" : (string)($val ?? '');
            }, $condResolved, -1, $replaced);

            if (!self::evaluateCondition($row, $condResolved)) {
                return false;
            }
        }

        return true;
    }

    private static function evaluateWhere(array $row, string $where, array $params): bool
    {
        return self::matchWhere($row, $where, $params);
    }

    private static function evaluateCondition(array $row, string $cond): bool
    {
        $cond = trim($cond);

        if (preg_match('/CURDATE\(\)/', $cond)) {
            $cond = str_replace('CURDATE()', "'" . date('Y-m-d') . "'", $cond);
        }

        if (preg_match('/^(\S+)\s+IN\s+\((.+)\)$/i', $cond, $m)) {
            $col = $m[1];
            $inValues = explode(',', $m[2]);
            $inValues = array_map(function ($v) {
                return trim($v, "'\" ");
            }, $inValues);
            $rowVal = (string)($row[$col] ?? '');
            return in_array($rowVal, $inValues);
        }

        if (preg_match('/^(\S+)\s+NOT\s+IN\s+\((.+)\)$/i', $cond, $m)) {
            $col = $m[1];
            $inValues = explode(',', $m[2]);
            $inValues = array_map(function ($v) {
                return trim($v, "'\" ");
            }, $inValues);
            $rowVal = (string)($row[$col] ?? '');
            return !in_array($rowVal, $inValues);
        }

        if (preg_match('/^(\S+)\s*!=\s*(.+)$/i', $cond, $m)) {
            $col = $m[1];
            $val = trim($m[2], "'\" ");
            return (string)($row[$col] ?? '') !== $val;
        }

        if (preg_match('/^(\S+)\s*>=\s*(.+)$/i', $cond, $m)) {
            $col = $m[1];
            $val = trim($m[2], "'\" ");
            $rowVal = $row[$col] ?? '';
            return is_numeric($rowVal) && is_numeric($val) ? (float)$rowVal >= (float)$val : (string)$rowVal >= $val;
        }

        if (preg_match('/^(\S+)\s*<\s*(.+)$/i', $cond, $m)) {
            $col = $m[1];
            $val = trim($m[2], "'\" ");
            $rowVal = $row[$col] ?? '';
            return is_numeric($rowVal) && is_numeric($val) ? (float)$rowVal < (float)$val : (string)$rowVal < $val;
        }

        if (preg_match('/^(\S+)\s*=\s*(.+)$/i', $cond, $m)) {
            $col = $m[1];
            $val = trim($m[2], "'\" ");
            return (string)($row[$col] ?? '') === $val;
        }

        return true;
    }

    private static function applyJoin(array $rows, array $join): array
    {
        $joinRows = self::load($join['table']);
        $alias = $join['alias'];
        $tableName = $join['table'];

        preg_match('/(\S+)\.(\S+)\s*=\s*(\S+)\.(\S+)/', $join['on'], $m);
        if (!$m) {
            return $rows;
        }

        $leftCol = $m[4];
        $rightCol = $m[2];
        $rightTableAlias = $m[1];
        $leftTableAlias = $m[3];

        $result = [];
        foreach ($rows as $row) {
            $matched = false;
            foreach ($joinRows as $jr) {
                $leftVal = $row[$leftCol] ?? null;
                $rightVal = $jr[$rightCol] ?? null;
                if ((string)$leftVal === (string)$rightVal) {
                    $merged = $row;
                    foreach ($jr as $k => $v) {
                        if ($k === 'id') {
                            $merged[$alias . '_id'] = $v;
                        } else {
                            $merged[$alias === $tableName ? $k : $alias . '_' . $k] = $v;
                        }
                    }
                    $result[] = $merged;
                    $matched = true;
                }
            }
            if ($join['type'] === 'LEFT' && !$matched) {
                $merged = $row;
                foreach ($joinRows[0] ?? [] as $k => $v) {
                    $merged[$alias === $tableName ? $k : $alias . '_' . $k] = null;
                }
                $result[] = $merged;
            }
        }

        return $result;
    }

    private static function applyGroupBy(array $rows, array $parsed): array
    {
        $groupCol = trim($parsed['groupBy']);
        if (str_contains($groupCol, '.')) {
            $parts = explode('.', $groupCol);
            $groupCol = $parts[1] ?? $parts[0];
        }

        $groups = [];
        foreach ($rows as $row) {
            $key = (string)($row[$groupCol] ?? 'null');
            if (!isset($groups[$key])) {
                $groups[$key] = [];
                $groups[$key]['_first'] = $row;
            }
            $groups[$key][] = $row;
        }

        $result = [];
        foreach ($groups as $key => $groupRows) {
            $row = $groupRows['_first'];

            if (preg_match('/COUNT\(\s*\*\s*\)\s+as\s+(\w+)/i', $parsed['columns'], $cm)) {
                $row['cnt'] = count($groupRows);
            }

            if (preg_match('/SUM\(\s*(\w+)\s*\)\s+as\s+(\w+)/i', $parsed['columns'], $sm)) {
                $sumCol = $sm[1];
                $sum = 0;
                foreach ($groupRows as $gr) {
                    $sum += (float)($gr[$sumCol] ?? 0);
                }
                $row['total'] = $sum;
            }

            if (preg_match('/GROUP_CONCAT\((.+?)(?:\s+SEPARATOR\s+\'([^\']*)\')?\)\s+as\s+(\w+)/i', $parsed['columns'], $gcm)) {
                $concatExpr = $gcm[1];
                $separator = $gcm[2] ?? ',';
                $alias = $gcm[3];

                $values = [];
                foreach ($groupRows as $gr) {
                    if (preg_match('/CONCAT\(\'([^\']*)\'\s*,\s*(\w+)\.(\w+)\s*,\s*\'([^\']*)\'\s*,\s*(\w+)\.(\w+)\s*,\s*\'([^\']*)\'/i', $concatExpr, $ccm)) {
                        $v = $ccm[1]
                            . ($gr[$ccm[3]] ?? '')
                            . $ccm[4]
                            . ($gr[$ccm[6]] ?? '')
                            . ($ccm[7] ?? '');
                        $values[] = $v;
                    } elseif (preg_match('/CONCAT\(\'([^\']*)\'\s*,\s*(\w+)\.(\w+)\)/i', $concatExpr, $ccm)) {
                        $v = $ccm[1] . ($gr[$ccm[3]] ?? '');
                        $values[] = $v;
                    }
                }
                $row[$alias] = implode($separator, $values);
            }

            unset($row['_first']);
            $result[] = $row;
        }

        return $result;
    }

    private static function applyOrderBy(array &$rows, string $orderBy): void
    {
        $parts = explode(',', $orderBy);
        $sort = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^(\S+)\s+(ASC|DESC)$/i', $part, $m)) {
                $col = $m[1];
                $dir = strtoupper($m[2]) === 'DESC' ? SORT_DESC : SORT_ASC;
            } else {
                $col = $part;
                $dir = SORT_DESC;
            }

            if (str_contains($col, '.')) {
                $parts2 = explode('.', $col);
                $col = $parts2[1] ?? $parts2[0];
            }

            $sort[] = ['col' => $col, 'dir' => $dir];
        }

        usort($rows, function ($a, $b) use ($sort) {
            foreach ($sort as $s) {
                $col = $s['col'];
                $va = $a[$col] ?? '';
                $vb = $b[$col] ?? '';
                if ($va != $vb) {
                    if ($s['dir'] === SORT_DESC) {
                        return $va < $vb ? 1 : -1;
                    }
                    return $va < $vb ? -1 : 1;
                }
            }
            return 0;
        });
    }

    private static function applyColumns(array $rows, string $columns, string $table): array
    {
        $colStr = trim($columns);

        if ($colStr === '*' || $colStr === '') {
            return $rows;
        }

        $result = [];
        foreach ($rows as $row) {
            $newRow = [];

            if (preg_match('/COUNT\(\s*\*\s*\)/i', $colStr)) {
                $newRow['cnt'] = $row['cnt'] ?? count($rows);
            }

            if (preg_match('/SUM\(\s*(\w+)\s*\)\s+as\s+(\w+)/i', $colStr, $sm)) {
                $newRow[$sm[2]] = $row['total'] ?? 0;
            }

            if (preg_match('/COALESCE\(\s*SUM\(\s*(\w+)\s*\)\s*,\s*(\d+)\s*\)\s+as\s+(\w+)/i', $colStr, $cm)) {
                $newRow[$cm[3]] = $row['total'] ?? (int)$cm[2];
            }

            if (preg_match('/GROUP_CONCAT\(.+\)\s+as\s+(\w+)/i', $colStr, $gcm)) {
                $newRow[$gcm[1]] = $row[$gcm[1]] ?? '';
            }

            $cols = explode(',', $colStr);
            foreach ($cols as $c) {
                $c = trim($c);
                if (str_contains(strtoupper($c), 'AS ')) {
                    if (preg_match('/(.+)\s+AS\s+(\w+)/is', $c, $m)) {
                        $expr = trim($m[1]);
                        $alias = $m[2];

                        if (preg_match('/^(\w+)\.(\w+)$/', $expr, $fm)) {
                            $newRow[$alias] = $row[$fm[2]] ?? $row[$fm[0]] ?? null;
                        } elseif (str_contains($expr, "'")) {
                            $newRow[$alias] = trim($expr, "'");
                        } elseif (isset($row[$alias])) {
                            $newRow[$alias] = $row[$alias];
                        } else {
                            $newRow[$alias] = $row[$expr] ?? null;
                        }
                    }
                } elseif (str_contains($c, '.')) {
                    $parts = explode('.', $c);
                    $colName = $parts[1] ?? $parts[0];
                    if (isset($row[$colName])) {
                        $newRow[$colName] = $row[$colName];
                    } elseif (isset($row[$c])) {
                        $newRow[$c] = $row[$c];
                    }
                } elseif (isset($row[$c])) {
                    $newRow[$c] = $row[$c];
                }
            }

            if (empty($newRow)) {
                $result[] = $row;
            } else {
                $result[] = $newRow;
            }
        }

        return $result;
    }
}
