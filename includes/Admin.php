<?php

namespace Zprint;

use \Zprint\Model\Location;

class Admin
{
	public function __construct()
	{
		if (!isset($_SESSION) && is_admin()) {
			session_start(['read_and_close' => true]);
		}
		\add_action('init', [static::class, 'showTemplatePreview']);
		\add_action('wp_ajax_zprint_reprint', [static::class, 'processAjaxCallReprint']);
		//\add_action('manage_shop_order_posts_custom_column', [static::class, 'ordersAdminColumn'], 10, 2);
		\add_action('woocommerce_order_actions_start', [static::class, 'orderAdminActions']);

		static::checkReprintNotice();
	}

	public static function checkReprintNotice()
	{
		if (is_admin() && isset($_SESSION['zprint_reprint'])) {
			session_start();
			$status = $_SESSION['zprint_reprint'];
			unset($_SESSION['zprint_reprint']);
			session_write_close();
			\add_action('admin_notices', function () use ($status) {
				if ($status) {
					?>
					<div class="notice notice-success">
						<p>Order is reprinting</p>
					</div>
					<?php
				} else {
					?>
					<div class="notice notice-error">
						<p>Reprint error</p>
					</div>
					<?php
				}
			});
		}
	}

	public static function ordersAdminColumn($column, $post_id)
	{
		if ($column === 'order_actions') {
			add_thickbox();
			$locations = Location::getAllFormatted();
			if (empty($locations)) {
				return;
			}
			$dialog_id = static::reprintDialog($locations, $post_id, 'index'); ?>
			<br clear="both">
			<p>
				<a href="#TB_inline?width=600&height=300&inlineId=<?= $dialog_id; ?>"
					 class="thickbox button"
					 style="width: 2em; height: 2em; line-height: 1.5em;text-align: center;"
					 title="Reprint">⎙</a>
			</p>
			<?php
		}
	}

	public static function orderAdminActions($post_id)
	{
		$locations = Location::getAllFormatted();
		if (empty($locations)) {
			return;
		}
		add_thickbox();
		$dialog_id = static::reprintDialog($locations, $post_id, 'post'); ?>
		<li class="wide">
			<a class="thickbox button" style="width: 100%;"
				 href="#TB_inline?width=600&height=300&inlineId=<?= $dialog_id; ?>"
				 title="Reprint">Reprint</a>
		</li>
		<?php
	}

	public static function processAjaxCallReprint()
	{
		if (wp_verify_nonce($_GET['_wpnonce'], 'reprint')) {
			if ($order_id = filter_var($_GET['order_id'], FILTER_VALIDATE_INT)) {
				Printer::reprintOrder($order_id, array_map('intval', $_GET['location']));
				session_start();
				$_SESSION['zprint_reprint'] = true;
				session_write_close();
				if ($_GET['type'] === 'post') {
					header('Location: ' . admin_url("post.php?post={$order_id}&action=edit"));
				} else {
					header('Location: ' . admin_url('edit.php?post_type=shop_order'));
				}
				exit;
			}
		}
		wp_die();
	}

	public static function showTemplatePreview()
	{
		if (is_admin() && isset($_GET['zprint_location'], $_GET['zprint_order'])) {
			$order = $_GET['zprint_order'];
			$order = wc_get_order($order);
			try {
				if ($id = filter_var($_GET['zprint_location'], FILTER_VALIDATE_INT)) {
					$location = new Location($id);
				} else {
					throw new \Exception('Wrong Argument');
				}
			} catch (\Exception $exception) {
				die('Error:' . $exception->getMessage());
			}

			if (!$order instanceof \WC_Order) {
				die('Error: Order not found');
			}
			header('Content-Type: ' . Document::formatToContentType($location->format));
			echo Document::generatePrint($order, $location->getData());
			exit;
		}
	}

	public static function reprintDialog($locations, $post_id, $type)
	{
		$dialog_id = 'zprint-dialog-' . $post_id;
		add_action('admin_footer', function () use ($dialog_id, $locations, $post_id, $type) {
			?>
			<div id="<?= $dialog_id; ?>" style="display:none;">
				<h1>Reprint Order <?= $post_id; ?></h1>
				<form action="<?= admin_url('admin-ajax.php'); ?>">
					<?php wp_nonce_field('reprint'); ?>
					<input type="hidden" name="action" value="zprint_reprint">
					<input type="hidden" name="type" value="<?= $type ?>">
					<input type="hidden" name="order_id" value="<?= $post_id ?>">
					<p>
						<label for="location-<?= $post_id ?>">Location:</label><br>
						<select name="location[]" id="location-<?= $post_id ?>" multiple>
							<?php foreach ($locations as $location) {
								?>
								<option
									value="<?= $location['id']; ?>"><?= $location['title']; ?></option>
								<?php
							} ?>
						</select>
					</p>
					<p>
						<input type="submit" class="button button-primary" value="Print">
					</p>
				</form>
			</div>
			<?php
		});
		return $dialog_id;
	}
}
