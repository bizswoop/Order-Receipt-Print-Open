<?php

namespace Zprint;

use Zprint\Aspect\Box;
use Zprint\Aspect\Page;
use Zprint\Model\Location;
use Zprint\Template\Index;
use Zprint\Template\Options;
use Zprint\Template\TemplateSettings;

return function (Location $location, $page, Page $setting_page) {
	wp_register_style('zprint_location-style', plugins_url('assets/location.css', PLUGIN_ROOT_FILE), ['thickbox'], PLUGIN_VERSION);
	wp_register_script('zprint_location-script', plugins_url('assets/location.js', PLUGIN_ROOT_FILE), ['jquery', 'media-upload', 'thickbox'], PLUGIN_VERSION);

	$roles = $setting_page->scope(function ($setting_page) {
		return Input::get('roles')->getValue(Box::get('location user roles'), null, $setting_page);
	});

	$users = get_users(['role__in' => $roles]);
	$users_values = array_map(function ($user) {
		return $user->display_name;
	}, $users);
	$users_keys = array_map(function ($user) {
		return $user->ID;
	}, $users);

	$users = array_combine($users_keys, $users_values);
	$printers = Printer::getPrinters();

	$formats = Location::getFormats();
	$orientations = Location::getOrientations();
	$templatesName = Location::getTemplates();
	$sizes = Location::getSizes();
	$templates = Templates::getTemplates();
	$templatesSlug = array_keys($templates);

	$templatesFormats = array_map(function ($template) {
		if ($template instanceof Index) {
			return array_keys(array_filter($template->getFormats()));
		} else {
			return ['html', 'plain'];
		}
	}, $templates);

	$templatesSettings = array_map(function ($template) {
		if ($template instanceof TemplateSettings) {
			return $template->getTemplateSettings();
		} else {
			return [
				'shipping' => [
					'cost' => true,
					'customer_details' => true,
					'billing_shipping_details' => true,
					'method' => true,
				],
				'total' => [
					'cost' => true
				]
			];
		}
	}, $templates);

	$templatesFormats = array_combine($templatesSlug, $templatesFormats);
	$templatesSettings = array_combine($templatesSlug, $templatesSettings);

	wp_localize_script('zprint_location-script', '_ZPRINT_TEMPLATES_', [
		'formats' => $templatesFormats,
		'settings' => $templatesSettings
	]);

	foreach ($templates as $template) {
		if (!$template instanceof Options || !$template instanceof Index) continue;
		?>
		<div id="template-<?= $template->getSlug(); ?>" hidden>
			<?= $template->renderOptions($location->getTemplateOption()); ?>
		</div>
	<?php } ?>
	<form method="post" id="location_form">
		<div class="form_wrapper">
			<div class="box">
				<h3><label for="title"><?php _e('Location title', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></label></h3>
				<input name="zpl_title" id="title" type="text" value="<?= $location->title ?>">
				<?php if ($location->getID() && Order::getSampleOrder()): ?>
					<br>
					<br>
					<input
						type="submit"
						class="button"
						name="test_print"
						value="<?php _e('Test print', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>"
					>
					<a class="button" target="_blank"
						 href="<?= add_query_arg(['zprint_location' => $location->getID(), 'zprint_order' => Order::getSampleOrder()], admin_url()); ?>">
						<?php _e('View Sample', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</a>
				<?php endif; ?>
			</div>
			<div class="box">
				<p>
					<label><?php _e('Web order printer', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></label>
					<label>
						<input name="zpl_web_order" type="checkbox"
									 id="web_order" <?php checked($location->enabledWEB); ?>>
						<?php _e('Enable', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label>
				</p>
				<?php if (defined('\ZPOS\ACTIVE') && \ZPOS\ACTIVE): ?>
					<p>
						<label><?php _e('POS order printer', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></label>
						<label>
							<input name="zpl_pos_order_only" type="checkbox"
										 id="pos_order_only" <?php checked($location->enabledPOS); ?>>
							<?php _e('Enable', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label>
					</p>
				<?php endif; ?>
			</div>
			<br>
			<div class="box">
				<h3><label for="printers"><?php _e('Printers', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></label></h3>
				<select id="printers"
								name="zpl_printers[]"
								multiple>
					<?php foreach ($printers as $printer => $name): ?>
						<option
							value="<?= $printer ?>" <?= in_array($printer, $location->printers) ? "selected" : ""; ?>><?= $name ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="box">
				<h3><label for="users"><?php _e('Users', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></label></h3>
				<select id="users" name="zpl_users[]" multiple>
					<?php foreach ($users as $user => $name): ?>
						<option
							value="<?= $user ?>" <?= in_array($user, $location->users) ? "selected" : ""; ?>><?= $name ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<br>

			<div class="box">
				<h3>
					<label for="template"><?php _e('Template', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></label>
					<div class="purpose_custom">
						<?php _e('Need Custom Templates?', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<a class="button" href="https://www.bizswoop.com/wp/print/templates">
							<?php _e('Buy', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</a>
					</div>
				</h3>
				<select id="template" name="zpl_template" required>
					<option value="" disabled hidden
									selected><?php _e('Select Template', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></option>
					<?php foreach ($templatesName as $template => $name): ?>
						<option
							value="<?= $template ?>" <?php selected($template, $location->template); ?>><?= $name ?></option>
					<?php endforeach; ?>
				</select>

				<p class="templateSetting" data-group="total" data-label="cost">
					<label><?php _e('Include Order Cost', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_total[cost]"
									 value="1" <?= $location->total['cost'] ? 'checked' : ''; ?>>
					</label>
				</p>
				<p class="templateSetting" data-group="shipping" data-label="cost">
					<label><?php _e('Include Shipping Cost', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_shipping[cost]"
									 value="1" <?= $location->shipping['cost'] ? 'checked' : ''; ?>>
					</label>
				</p>
				<p class="templateSetting" data-group="shipping" data-label="billing_shipping_details">
					<label>
						<?php _e('Include Billing & Shipping Details', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_shipping[billing_shipping_details]"
									 value="1" <?= $location->shipping['billing_shipping_details'] ? 'checked' : ''; ?>>
					</label>
				</p>
				<p class="templateSetting" data-group="shipping" data-label="customer_details">
					<label>
						<?php _e('Include Customer Details', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_shipping[customer_details]"
									 value="1" <?= $location->shipping['customer_details'] ? 'checked' : ''; ?>>
					</label>
				</p>
				<p class="templateSetting" data-group="shipping" data-label="method">
					<label>
						<?php _e('Include Shipping Method', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_shipping[method]"
									 value="1" <?= $location->shipping['method'] ? 'checked' : ''; ?>>
					</label>
				</p>
				<p class="templateSetting" data-group="shipping" data-label="delivery_pickup_type">
					<label>
						<?php _e('Include Pickup or Delivery Type', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_shipping[delivery_pickup_type]"
									 value="1" <?= $location->shipping['delivery_pickup_type'] ? 'checked' : ''; ?>>
					</label>
				</p>
				<p class="text-centered">
					<a href="https://www.bizswoop.com/wp/print/developer">
						<?php _e('Developer Documentation', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</a>
				</p>
			</div>

			<div class="box">
				<h3><?php _e('Appearance', 'Print-Google-Cloud-Print-GCP-WooCommerce') ?></h3>
				<script>
					jQuery(document).ready(function($) {
						$("#upload_logo").click(function(e) {
							e.preventDefault();
							tb_show("Upload", "media-upload.php?type=image&referer=logo&TB_iframe=true&post_id=0", false);
						});
						$("#remove_logo").click(function(e) {
							e.preventDefault();
							$("#logo_src, #logo").removeAttr("value");
							$("#logo_preview").removeAttr("src");
						});
						window.send_to_editor = function(html) {
							var image_url = $(html).attr("src");
							var id_attach = $(html).attr("class").match(/\d+/g);
							id_attach = id_attach[0];
							var name = "referer";
							name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
							var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
								results = regex.exec(jQuery("#TB_iframeContent").attr("src"));
							var id = results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
							$("#" + id).val(id_attach);
							$("#" + id + "_src").attr({ "value": image_url });
							$("#" + id + "_preview").attr({ "src": image_url }).show();
							tb_remove();
						};
					});
				</script>
				<style>
					#logo_preview, #logo_src {
						display: none;
					}

					#logo_preview[src], #logo_src[value] {
						display: inline-block;
					}
				</style>
				<div>
					<?php
					$logo = $location->appearance['logo'];
					if ($logo !== null) {
						list($src) = wp_get_attachment_image_src($logo, 'full');
					} else {
						$src = null;
					}
					?>
					<p style="display: flex">
						<label style="flex-grow: 1; line-height: 28px;">Logo</label>
						<button class="button" id="upload_logo" style="margin-left: 5px">Upload</button>
						<button class="button" id="remove_logo" style="margin-left: 5px">Remove</button>
					</p>
					<p>
						<input type="text" readonly id="logo_src" <?= ($src) ? 'value="' . $src . '"' : ''; ?>>
						<input type="hidden" readonly id="logo" value="<?= $logo ?>" name="zpl_appearance_logo">
					</p>
					<p style="text-align: center;">
						<img
							id="logo_preview"
							alt=""
							style="max-width: 100%; height: auto"
							<?= ($src) ? 'src="' . $src . '"' : ''; ?>
						>
					</p>
				</div>
				<p>
					<label for="check_header">Check Header</label>
					<input
						type="text"
						id="check_header"
						value="<?= $location->appearance['Check Header']; ?>"
						name="zpl_appearance_check_header"
					>
				</p>
				<p>
					<label for="company_name">Company Name</label>
					<input
						type="text"
						id="company_name"
						value="<?= $location->appearance['Company Name']; ?>"
						name="zpl_appearance_company_name"
					>
				</p>
				<p>
					<label for="company_info">Company Info</label>
					<input
						type="text"
						id="company_info"
						value="<?= $location->appearance['Company Info']; ?>"
						name="zpl_appearance_company_info"
					>
				</p>
				<p>
					<label for="order_details_header">Order Details Header</label>
					<input
						type="text"
						id="order_details_header"
						value="<?= $location->appearance['Order Details Header']; ?>"
						name="zpl_appearance_order_details_header"
					>
				</p>
				<p>
					<label for="footer_information_1">Footer Information #1</label>
					<input
						type="text"
						id="footer_information_1"
						value="<?= $location->appearance['Footer Information #1']; ?>"
						name="zpl_appearance_footer_information_1"
					>
				</p>
				<p>
					<label for="footer_information_2">Footer Information #2</label>
					<input
						type="text"
						id="footer_information_2"
						value="<?= $location->appearance['Footer Information #2']; ?>"
						name="zpl_appearance_footer_information_2"
					>
				</p>

				<?php if (Document::brandingStatus()) { ?>
					<br>
					<p style="text-align: center">
						Powered by BizSwoop
						<a href="https://www.bizswoop.com/wp/print/branding/" target="_blank">
							Remove Branding
						</a>
					</p>
				<?php } ?>
			</div>

			<div id="custom_options" class="box" hidden>
				<h3>Custom Template Options</h3>
				<div class="content">

				</div>
			</div>

			<?= Admin\Location::renderBoxes($location, function ($name, $slug, $render) { ?>
				<div id="<?= $slug ?>" class="box">
					<h3><?= $name ?></h3>
					<div class="content">
						<?= $render ?>
					</div>
				</div>
			<?php }); ?>

			<hr>

			<h3><?php _e('Settings', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?></h3>

			<div class="box wide orientation">
				<label for="orientation">
					<?php _e('Page Orientation', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</label>
				<select id="orientation" name="zpl_orientation" required class="orientation_select">
					<option value="" disabled hidden selected>
						<?php _e('Select Orientation', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</option>
					<?php foreach ($orientations as $orientation => $name): ?>
						<option
							value="<?= $orientation ?>" <?php selected($orientation, $location->orientation); ?>
						>
							<?= $name ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="box" id="formatBox">
				<p>
					<label for="format">
						<?php _e('Output Format', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label><br>
					<select id="format" name="zpl_format" required>
						<option value="" disabled hidden selected>
							<?php _e('Select Format', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</option>
						<?php foreach ($formats as $format => $name): ?>
							<option
								value="<?= $format ?>" <?php selected($format, $location->format); ?>><?= $name ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p id="symbolsLengthContainer" hidden>
					<label for="symbolsLength">
						<?php _e('Plain text format symbols width', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label><br>
					<input id="symbolsLength" name="zpl_symbolsLength" type="number" disabled required
								 value="<?= $location->symbolsWidth ?>">
				</p>

				<p id="printSymbolsDebugContainer" hidden>
					<label for="printSymbolsDebug">
						<?php _e('Print symbols for layout debug', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label>
					<input id="printSymbolsDebug" name="zpl_printSymbolsDebug" type="checkbox" disabled
								 value="1" <?= $location->printSymbolsDebug ? 'checked' : '' ?>>
				</p>

				<div id="fontContainer" hidden>
					<p>
						<label for="fontSize">
							<?php _e('Basic Font Size (px)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label><br>
						<input id="fontSize" name="zpl_fontSize" type="number" disabled required
									 value="<?= $location->font['basicSize'] ?>">
					</p>
					<p>
						<label for="headerSize">
							<?php _e('Header Font Size (px)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label><br>
						<input id="headerSize" name="zpl_headerSize" type="number" disabled required
									 value="<?= $location->font['headerSize'] ?>">
					</p>
					<p>
						<label for="fontWeight">
							<?php _e('Basic Font Weight (CSS value)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label><br>
						<input id="fontWeight" name="zpl_fontWeight" type="number" disabled required
									 value="<?= $location->font['basicWeight'] ?>">
					</p>
					<p>
						<label for="headerWeight">
							<?php _e('Header Font Weight (CSS value)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label><br>
						<input id="headerWeight" name="zpl_headerWeight" type="number" disabled required
									 value="<?= $location->font['headerWeight'] ?>">
					</p>
				</div>
			</div>

			<div class="box">
				<p>
					<label for="custom_margins">
						<?php _e('Custom Margins', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						<input type="checkbox" name="zpl_margins_custom" id="custom_margins"
									 value="1" <?= ($location->margins === null) ? '' : 'checked'; ?>>
					</label>
				</p>
				<p class="margins" id="margins" hidden>
					<label for="margins">
						<?php _e('Layout Margins', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label><br>

					<label for="top_margin">
						<?php _e('Top Margin', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label>
					<input name="zpl_margins[0]" id="top_margin" type="number" disabled required
								 value="<?= $location->margins[0] ?>"><?php _e('mm', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?><br>
					<label for="right_margin">
						<?php _e('Right Margin', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label>
					<input name="zpl_margins[1]" id="right_margin" type="number" disabled required
								 value="<?= $location->margins[1] ?>"><?php _e('mm', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?><br>
					<label for="bottom_margin">
						<?php _e('Bottom Margin', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label>
					<input name="zpl_margins[2]" id="bottom_margin" type="number" disabled required
								 value="<?= $location->margins[2] ?>"><?php _e('mm', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?><br>
					<label for="left_margin">
						<?php _e('Left Margin', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label>
					<input name="zpl_margins[3]" id="left_margin" type="number" disabled required
								 value="<?= $location->margins[3] ?>"><?php _e('mm', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
				</p>
			</div>
			<br>

			<div class="box">
				<p>
					<label for="size">
						<?php _e('Layout Size', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
					</label><br>
					<select id="size" name="zpl_size" required>
						<option value="" disabled hidden selected>
							<?php _e('Select Size', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</option>
						<?php foreach ($sizes as $value => $name): ?>
							<option
								value="<?= $value ?>" <?php selected($value, $location->size); ?>
							>
								<?= $name ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>

				<div id="custom_sizes">
					<p>
						<label for="width">
							<?php _e('Custom Width (mm)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label><br>
						<i>
							<?php _e('Note: use 0mm for automatic width (based on printer setting)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</i><br>
						<input type="number" id="width" name="zpl_width" value="<?= $location->width ?>">
					</p>
					<p id="height_field" hidden>
						<label for="height">
							<?php _e('Custom Height (mm)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</label><br>
						<i>
							<?php _e('Note: use 0mm for continuous feed (some printers may not support this function)', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>
						</i><br>
						<input type="number" id="height" name="zpl_height" value="<?= $location->height ?>">
					</p>
				</div>
			</div>
		</div>

		<p class="submit">
			<input
				type="submit"
				name="submit"
				id="submit"
				class="button button-primary"
				value="<?php _e('Save Changes', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>"
			>
			<?php if ($location->getID()): ?>
				<input
					type="submit"
					class="button delete-button"
					name="delete"
					value="<?php _e('Delete', 'Print-Google-Cloud-Print-GCP-WooCommerce'); ?>"
				>
				<input type="hidden" name="id" value="<?= $location->getID(); ?>">
			<?php endif; ?>
		</p>
	</form>
	<?php
	wp_enqueue_style('zprint_location-style');
	wp_enqueue_script('zprint_location-script');
};
