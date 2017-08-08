<?php 
	$footer_style = ot_get_option('footer_style', 'style1');
	$newsletter = ot_get_option('newsletter');
	$footer_products = ot_get_option('footer_products');
?>
		</div><!-- End role["main"] -->
		
		<?php if (ot_get_option('footer') != 'off') { ?>
			<!-- Start Footer -->
			<?php 
				get_template_part( 'inc/footer/footer-'.ot_get_option('footer_style','style1').'' );
			?>
			<!-- End Footer -->
		<?php } ?>
	
	</section> <!-- End #content-container -->

</div> <!-- End #wrapper -->
<?php if ($newsletter != 'off') { do_action( 'thb_newsletter' ); } ?>
<?php echo ot_get_option('ga'); ?>
<?php 
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */
	 wp_footer(); 
?>

<script type="text/javascript">
    jQuery(document).ready(function($)
    { 
			function DropDown(el) {
				this.dd = el;
				this.initEvents();
			}
			DropDown.prototype = {
				initEvents : function() {
					var obj = this;

					obj.dd.on('click', function(event){
						jQuery(this).toggleClass('active');
						event.stopPropagation();
					});	
				}
			}

			jQuery(function() {

				var dd = new DropDown( jQuery('#dd') );

				jQuery(document).click(function() {
					// all dropdowns
					jQuery('.wrapper-dropdown-2').removeClass('active');
				});

			});

	});
</script>

</body>
</html>