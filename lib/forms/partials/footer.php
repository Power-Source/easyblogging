</div> <!-- inner -->
</div> <!-- primary-right -->
</div> <!-- wpbody-content -->
</div> <!-- wpwrap -->


<?php
//do_action('admin_print_footer_scripts');
do_action("admin_footer-" . $GLOBALS['hook_suffix']);
if (function_exists('wp_print_footer_scripts')) {
	wp_print_footer_scripts();
}
?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>