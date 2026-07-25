#!/bin/bash
# Mobaro Deployment Build Script
# Creates a production-ready ZIP for cPanel hosting
# Usage: bash deploy/build.sh

set -e

SRC="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$SRC/deploy/public_html"
ZIP="$SRC/deploy/mobaro-deploy.zip"

echo "=== Mobaro Deployment Build ==="
echo "Source: $SRC"
echo "Output: $OUT"
echo ""

# 1. Clean output directory (keep our pre-made files)
echo "[1/7] Cleaning output directory..."
rm -rf "$OUT/app" "$OUT/public" "$OUT/vendor" "$OUT/storage"
mkdir -p "$OUT/app" "$OUT/public" "$OUT/storage"
mkdir -p "$OUT/storage/cache" "$OUT/storage/logs" "$OUT/storage/sessions" "$OUT/storage/data"
mkdir -p "$OUT/public/uploads" "$OUT/public/assets/uploads/gallery" "$OUT/public/assets/uploads/videos"

# 2. Copy app/ (exclude tests, analysis, skills)
echo "[2/7] Copying app/..."
rsync -a --exclude='tests/' --exclude='analysis-output/' --exclude='skills/' \
    "$SRC/app/" "$OUT/app/"

# 3. Copy public/ (exclude dev artifacts, image cache)
echo "[3/7] Copying public/..."
rsync -a --exclude='router.php' \
    --exclude='assets/images/cache/' \
    "$SRC/public/" "$OUT/public/"

# Ensure .htaccess and .gitkeep exist in upload dirs
touch "$OUT/public/uploads/.gitkeep"
cp "$SRC/public/uploads/.htaccess" "$OUT/public/uploads/.htaccess" 2>/dev/null || true
cp "$SRC/public/assets/uploads/.htaccess" "$OUT/public/assets/uploads/.htaccess" 2>/dev/null || true
cp "$SRC/public/assets/images/.htaccess" "$OUT/public/assets/images/.htaccess" 2>/dev/null || true

# 4. Copy config files
echo "[4/7] Copying config files..."
cp "$SRC/composer.json" "$OUT/"
cp "$SRC/composer.lock" "$OUT/"
cp "$SRC/LICENSE" "$OUT/"

# 5. Create storage guards
echo "[5/7] Creating storage guards..."
cat > "$OUT/storage/.htaccess" << 'EOF'
Order Deny,Allow
Deny from all
EOF

cat > "$OUT/storage/index.php" << 'EOF'
<?php
header('HTTP/1.0 403 Forbidden');
exit;
EOF

cat > "$OUT/storage/cache/.gitignore" << 'EOF'
*
!.gitignore
EOF

cat > "$OUT/storage/logs/.gitignore" << 'EOF'
*
!.gitignore
EOF

cat > "$OUT/storage/sessions/.gitignore" << 'EOF'
*
!.gitignore
EOF

# 6. Install production dependencies
echo "[6/7] Installing production Composer dependencies..."
cd "$OUT"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction 2>&1
else
    echo "  WARNING: composer not found. Copying vendor/ from project..."
    rsync -a --exclude='phpunit/' --exclude='squizlabs/' --exclude='php_codesniffer/' \
        --exclude='php-code-sniffer/' --exclude='sebastian/' --exclude='myclabs/' \
        --exclude='theseer/' --exclude='phar-io/' --exclude='nikic/' \
        --exclude='phpoption/' --exclude='graham-campbell/' \
        "$SRC/vendor/" "$OUT/vendor/"
    echo "  NOTE: Run 'composer install --no-dev --optimize-autoloader' on the server if autoloader issues occur."
fi
cd "$SRC"

# 7. Clean and create ZIP
echo "[7/7] Creating ZIP archive..."
rm -f "$ZIP"

# Remove old install guide from previous builds
rm -f "$OUT/راهنمای-نصب.txt.bak"

cd "$SRC/deploy"
zip -r "$ZIP" \
    public_html/ \
    "راهنمای-نصب.txt" \
    -x "public_html/storage/cache/*" \
    -x "public_html/storage/logs/*" \
    -x "public_html/storage/sessions/*" \
    -x "public_html/.DS_Store" \
    -x "*/.DS_Store"

cd "$SRC"

# Stats
FILES=$(unzip -l "$ZIP" 2>/dev/null | tail -1 | awk '{print $2}')
SIZE=$(ls -lh "$ZIP" | awk '{print $5}')

echo ""
echo "=== Build Complete ==="
echo "ZIP: $ZIP"
echo "Files: $FILES"
echo "Size: $SIZE"
echo ""
echo "Next steps:"
echo "  1. Upload mobaro-deploy.zip to cPanel File Manager"
echo "  2. Extract in public_html/"
echo "  3. Rename .env.example to .env and edit credentials"
echo "  4. Import database/install.sql in phpMyAdmin"
echo "  5. Visit https://mobaro.ir/setup-admin.php"
echo "  6. Delete setup-admin.php after creating admin"
