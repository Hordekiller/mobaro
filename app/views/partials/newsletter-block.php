<?php

declare(strict_types=1);

?>
<script>
const servicesData = <?= json_encode($services, JSON_UNESCAPED_UNICODE) ?>;
const productsData = <?= json_encode($products, JSON_UNESCAPED_UNICODE) ?>;
</script>
