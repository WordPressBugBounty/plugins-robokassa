<?php

use Robokassa\Payment\Util;

register_activation_hook(__DIR__ . '/wp_robokassa.php', 'robokassa_plugin_activated');
register_deactivation_hook(__DIR__ . '/wp_robokassa.php', 'robokassa_plugin_deactivated');

function robokassa_plugin_activated() {
	robokassa_notify_status_change('enabled');
}

function robokassa_plugin_deactivated() {
	robokassa_notify_status_change('disabled');
}

function robokassa_notify_status_change($status) {
	$apiUrl = 'https://pulse.robokassa.com/api/module-status';
	$apiKey = 'robokassa-plugin-stat-key-3953';

	$site_url = Util::siteUrl();
	$merchantId = get_option('robokassa_payment_MerchantLogin');

	$payload = [
		'cms' => 'wordpress',
		'merchant_id' => $merchantId ?: 'unknown',
		'site_id' => $site_url,
		'status' => $status,
		'reported_at' => current_time('Y-m-d H:i:s'),
	];

	wp_remote_post($apiUrl, [
		'headers' => [
			'Content-Type'  => 'application/json',
			'X-API-KEY'     => $apiKey,
		],
		'body' => json_encode($payload),
		'timeout' => 5,
	]);

	robokassa_payment_DEBUG("[Robokassa] Статус $status отправлен на $apiUrl");
}