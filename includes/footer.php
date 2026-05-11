<?php
/**
 * includes/footer.php
 * Shared closing HTML for all pages.
 *
 * Usage — at the bottom of any page:
 *
 *   <?php
 *   $extra_js = "console.log('page loaded');";  // Optional extra JS
 *   include 'includes/footer.php';
 *   ?>
 */
$extra_js = $extra_js ?? '';
?>

<?php if(isset($page_type) && $page_type === 'admin'): ?>
<!-- Bootstrap JS (admin pages only) -->
<script>
(function(){
    var s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
    s.onerror = function(){
        var l = document.createElement('script');
        l.src = 'bootstrap/bootstrap.bundle.min.js';
        document.head.appendChild(l);
    };
    document.head.appendChild(s);
})();
</script>
<?php endif; ?>

<?php if(!empty($extra_js)): ?>
<script><?= $extra_js ?></script>
<?php endif; ?>

</body>
</html>