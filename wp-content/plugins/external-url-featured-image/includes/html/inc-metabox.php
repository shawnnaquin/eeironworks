<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div id="dcmsefi_Container" style="display:none;">
	<input id="dcmsefi_url" type="url" name="dcmsefi_url" placeholder="URL" style="width:50%;" />
	<a id="dcmsefi_preview" class="button" style="text-align:center;width:46%;display:inline-block;"><?php _e('Preview', DCMS_EUFI_DOMAIN) ?></a>
	
	<input id="dcmsefi_alt" type="text" name="dcmsefi_alt" placeholder="Alt text" style="width:100%">

	<div style="width:100%;border:1px dotted #d1d1d1;min-height:20px;margin-top:8px;text-align:center;color:#d1d1d1;">
		<span id="dcmsefi_noimg"><?php _e('No image', DCMS_EUFI_DOMAIN); ?></span>
		<img id="dcmsefi_img" style="max-width:100%;height:auto;" />
	</div>

	<a id="dcmsefi_remove" class="button" style="margin-top:4px;"><?php _e('Remove Image', DCMS_EUFI_DOMAIN) ?></a>
</div>

<script>

	jQuery(document).ready(function($){

		// Inicialization
		$('#dcmsefi_Container').show();

		<?php if ( ! $hasdata ): ?>
			$('#dcmsefi_img').attr('src','');
			$('#dcmsefi_noimg').show();
			$('#dcmsefi_alt').hide().val('');
			$('#dcmsefi_remove').hide();
			$('#dcmsefi_url').show().val('');
			$('#dcmsefi_preview').show();
		<?php else: ?>
			$('#dcmsefi_img').attr('src',"<?php echo $img; ?>");
	    	$('#dcmsefi_noimg').hide();
	    	$('#dcmsefi_alt').show().val("<?php echo $alt; ?>");
	    	$('#dcmsefi_remove').show();
	    	$('#dcmsefi_url').hide().val("<?php echo $img ?>");
	    	$('#dcmsefi_preview').hide();
		<?php endif; ?>

		// Preview
		$('#dcmsefi_preview').click(function(e){
			
			e.preventDefault();
			imgUrl = $('#dcmsefi_url').val();
			
			if ( imgUrl != '' ){
				$("<img>", { // Url validation
						    src: imgUrl,
						    error: function() {alert('<?php _e('Error URL Image', DCMS_EUFI_DOMAIN) ?>')},
						    load: function() {
						    	$('#dcmsefi_img').attr('src',imgUrl);
						    	$('#dcmsefi_noimg').hide();
						    	$('#dcmsefi_alt').show();
						    	$('#dcmsefi_remove').show();
						    	$('#dcmsefi_url').hide();
						    	$('#dcmsefi_preview').hide();
						    }
				});
			} //-- if 
		}); //-- click Preview

		// Remove
		$('#dcmsefi_remove').click(function(e){

			e.preventDefault();
			$('#dcmsefi_img').attr('src','');
			$('#dcmsefi_noimg').show();
	    	$('#dcmsefi_alt').hide().val('');
	    	$('#dcmsefi_remove').hide();
	    	$('#dcmsefi_url').show().val('');
	    	$('#dcmsefi_preview').show();

		}); //-- click Remove

	});

</script>