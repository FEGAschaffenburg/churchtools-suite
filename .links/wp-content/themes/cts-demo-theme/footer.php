</main>

<footer class="site-footer">
<div class="container">
<!-- Demo Disclaimer Banner (kurz) -->
<div class="demo-disclaimer">
<div class="disclaimer-icon">⚠️</div>
<div class="disclaimer-content">
<strong>Demo-Website:</strong> Alle Events sind Beispieldaten. Software ohne Gewährleistung ("as is").
→ <a href="<?php echo home_url( '/haftungsausschluss/' ); ?>" style="color: #92400e; text-decoration: underline; font-weight: 600;">Vollständiger Haftungsausschluss</a>
</div>
</div>

<div class="footer-bottom">
<div class="footer-left">
<span class="footer-copyright">© <?php echo date( 'Y' ); ?> FEG Aschaffenburg</span>
</div>
<div class="footer-right">
<?php
if ( has_nav_menu( 'footer' ) ) {
wp_nav_menu( array(
'theme_location' => 'footer',
'container' => false,
'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
'depth' => 1,
'link_after' => ' |'
) );
}
?>
</div>
</div>
</div>
</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
