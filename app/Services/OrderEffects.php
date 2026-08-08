<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;

final class OrderEffects
{
    public static function apply(int $orderId, string $trackingCode, int $userId, int $total, ?string $couponCode, int $couponDiscount): void
    {
        $orderItems = Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);
        $courseItems = [];

        foreach ($orderItems as $oi) {
            $productId = (int) $oi['product_id'];
            $itemType = (string) ($oi['item_type'] ?? '');
            if ($itemType === '') {
                $itemType = self::resolveLegacyItemType($productId);
            }
            if ($itemType === 'course') {
                $courseItems[] = ['id' => $productId];
                continue;
            }
            Database::query("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?", [(int) $oi['quantity'], $productId]);
        }

        if ($couponCode && $couponDiscount > 0) {
            Database::query("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?", [$couponCode]);
        }

        $pointsEarned = floor($total / 10000);
        if ($pointsEarned > 0) {
            Database::insert('transactions', [
                'user_id' => $userId,
                'type' => 'points_earn',
                'amount' => $pointsEarned,
                'description' => "امتیاز خرید سفارش {$trackingCode}",
            ]);
            Database::query("UPDATE users SET points = points + ? WHERE id = ?", [$pointsEarned, $userId]);
        }

        self::enrollCourseItems($userId, $courseItems);
    }

    public static function resolveLegacyItemType(int $productId): string
    {
        if (Database::fetch("SELECT id FROM products WHERE id = ?", [$productId])) {
            return 'product';
        }
        if (Database::fetch("SELECT id FROM courses WHERE id = ?", [$productId])) {
            return 'course';
        }
        return 'product';
    }

    private static function enrollCourseItems(int $userId, array $courseItems): void
    {
        $courseIds = array_map(fn($c) => $c['id'], $courseItems);
        if (empty($courseIds)) {
            return;
        }

        $uniqueIds = array_values(array_unique($courseIds));
        $placeholders = implode(',', array_fill(0, count($uniqueIds), '?'));
        $existingEnrollments = Database::fetchAll(
            "SELECT course_id FROM course_enrollments WHERE user_id = ? AND course_id IN ({$placeholders})",
            array_merge([$userId], $uniqueIds)
        );
        $existingIds = array_column($existingEnrollments, 'course_id');

        foreach ($courseItems as $course) {
            if (in_array($course['id'], $existingIds)) {
                continue;
            }
            Database::insert('course_enrollments', [
                'user_id' => $userId,
                'course_id' => $course['id'],
                'progress' => 0,
            ]);
            Database::query("UPDATE courses SET students = students + 1 WHERE id = ?", [$course['id']]);
        }
    }
}
