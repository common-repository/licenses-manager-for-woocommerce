<?php

namespace QuadLayers\WLM\Backend\Controller\Reports\View;

use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;
use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;
use QuadLayers\WLM\Models\Product\License\Load as Model_Product_License;

class Activations_By_Product extends \WC_Admin_Report {

	public $chart_colours      = array();
	public $product_ids        = array();
	public $product_ids_titles = array();

	public function __construct() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['product_ids'] ) && is_array( $_GET['product_ids'] ) ) {
			$this->product_ids = array_filter( array_map( 'absint', $_GET['product_ids'] ) );
		} elseif ( isset( $_GET['product_ids'] ) ) {
			$this->product_ids = array_filter( array( absint( $_GET['product_ids'] ) ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public function get_product_activations_report_data() {

		$product_ids = $this->product_ids;
		$date_start  = $this->start_date;
		$date_end    = strtotime( '+1 DAY', $this->end_date );

		$activations = Model_Activation_Mapper::get(
			array(
				'product_ids'        => $product_ids,
				'license_date_start' => $date_start,
				'license_date_end'   => $date_end,
			)
		);

		return (array) $activations;
	}

	public function get_product_licenses_report_data() {

		$product_ids = $this->product_ids;
		$date_start  = $this->start_date;
		$date_end    = strtotime( '+1 DAY', $this->end_date );

		$licenses = Model_License_Mapper::get(
			array(
				'product_ids'        => $product_ids,
				'license_date_start' => $date_start,
				'license_date_end'   => $date_end,
			)
		);

		return (array) $licenses;
	}

	private function round_chart_totals( $amount ) {
		if ( is_array( $amount ) ) {
			return array( $amount[0], wc_format_decimal( $amount[1], 0 ) );
		} else {
			return wc_format_decimal( $amount, 0 );
		}
	}

	public function get_chart_legend() {

		if ( empty( $this->product_ids ) ) {
			return array();
		}

		$legend = array();

		$this->report_data = new \stdClass();

		foreach ( $this->product_ids as $product_id ) {

			$model_product = new Model_Product_License( $product_id );

			if ( ! $model_product->is_qlwlm() ) {
				continue;
			}

			$product_ids = $model_product->get_children();

			if ( ! $product_ids ) {
				continue;
			}

			$this->product_ids = array_merge( $product_ids, (array) $this->product_ids );

		}

		$this->report_data->activations = $this->get_product_activations_report_data();
		$this->report_data->licenses    = $this->get_product_licenses_report_data();

		$legend[] = array(
			'title'            => sprintf( esc_html__( '%s activations for the selected items', 'licenses-manager-for-woocommerce' ), '<strong>' . count( $this->report_data->activations ) . '</strong>' ),
			'color'            => $this->chart_colours['sales_amount'],
			'highlight_series' => 1,
		);

		$legend[] = array(
			'title'            => sprintf( esc_html__( '%s licenses for the selected items', 'licenses-manager-for-woocommerce' ), '<strong>' . count( $this->report_data->licenses ) . '</strong>' ),
			'color'            => $this->chart_colours['item_count'],
			'highlight_series' => 0,
		);

		return $legend;
	}

	public function output_report() {

		$ranges = array(
			'year'       => esc_html__( 'Year', 'licenses-manager-for-woocommerce' ),
			'last_month' => esc_html__( 'Last month', 'licenses-manager-for-woocommerce' ),
			'month'      => esc_html__( 'This month', 'licenses-manager-for-woocommerce' ),
			'7day'       => esc_html__( 'Last 7 days', 'licenses-manager-for-woocommerce' ),
		);

		$this->chart_colours = array(
			'sales_amount' => '#8fdece',
			'item_count'   => '#1abc9c',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_range = ! empty( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ), true ) ) {
			$current_range = '7day';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	public function get_chart_widgets() {

		$widgets = array();

		if ( ! empty( $this->product_ids ) ) {
			$widgets[] = array(
				'title'    => esc_html__( 'Showing reports for:', 'licenses-manager-for-woocommerce' ),
				'callback' => array( $this, 'current_filters' ),
			);
		}

		$widgets[] = array(
			'title'    => '',
			'callback' => array( $this, 'products_widget' ),
		);

		return $widgets;
	}

	public function count_activations( $product_id ) {

		/*
			$i = 0;

			foreach ( $this->report_data->activations as $activation ) {
				if ( $activation->get_product_id() == $product_id ) {
					$i++;
				}
			}

			return $i;
		*/

		return count( $this->report_data->activations );
	}

	public function current_filters() {
		?>
			<table cellspacing="0" style="border-bottom: 1px solid #e5e5e5;">
				<?php
				if ( $this->product_ids ) {
					foreach ( $this->product_ids as $product_id ) {

						// phpcs:ignore WordPress.Security.NonceVerification.Recommended
						if ( isset( $_GET['product_ids'] ) ) {
							// phpcs:ignore WordPress.Security.NonceVerification.Recommended
							if ( count( $this->product_ids ) > 1 && $product_id === $_GET['product_ids'] ) {
								continue;
							}
						}

						$model_product = new Model_Product_License( $product_id );

						if ( ! $model_product->is_qlwlm() ) {
							continue;
						}

						?>
							<tr>
								<td class="count"><?php echo esc_html( $model_product->get_id() ); ?></td>
								<td class="name"><a href="<?php echo esc_url( add_query_arg( 'product_ids', $model_product->get_id() ) ); ?>"><?php echo esc_html( $model_product->get_name() ); ?></a></td>
								<td class="sparkline"><?php echo esc_html( $this->count_activations( $product_id ) ); ?></td>							
							</tr>
						<?php

					}
				} else {
					?>
					<tr>
						<td colspan="3"><?php echo esc_html__( 'No products found in range', 'licenses-manager-for-woocommerce' ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		<p>
			<a class="button" href="<?php echo esc_url( remove_query_arg( 'product_ids' ) ); ?>"><?php echo esc_html__( 'Reset', 'licenses-manager-for-woocommerce' ); ?></a>
		</p>
		<?php
	}

	public function products_widget() {
		?>
		<h4 class="section_title"><span><?php esc_html_e( 'Product search', 'licenses-manager-for-woocommerce' ); ?></span></h4>
		<div class="section">
			<form method="GET">
			<div>
				<select class="wc-product-search" style="width:203px;" multiple="multiple" id="product_ids" name="product_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'licenses-manager-for-woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations"></select>
				<button type="submit" class="submit button" value="<?php esc_attr_e( 'Show', 'licenses-manager-for-woocommerce' ); ?>"><?php esc_html_e( 'Show', 'licenses-manager-for-woocommerce' ); ?></button>
				<input type="hidden" name="range" value="
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				echo ( ! empty( $_GET['range'] ) ) ? esc_attr( wp_unslash( $_GET['range'] ) ) : '';
				?>
				" />
				<input type="hidden" name="start_date" value="
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : '';
				?>
				" />
				<input type="hidden" name="end_date" value="
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : '';
				?>
				" />
				<input type="hidden" name="page" value="
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				echo ( ! empty( $_GET['page'] ) ) ? esc_attr( wp_unslash( $_GET['page'] ) ) : '';
				?>
				" />
				<input type="hidden" name="tab" value="
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				echo ( ! empty( $_GET['tab'] ) ) ? esc_attr( wp_unslash( $_GET['tab'] ) ) : '';
				?>
				" />
				<input type="hidden" name="report" value="
				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				echo ( ! empty( $_GET['report'] ) ) ? esc_attr( wp_unslash( $_GET['report'] ) ) : '';
				?>
				" />
			<?php wp_nonce_field( 'custom_range', 'wc_reports_nonce', false ); ?>
			</div>
			</form>
		</div>
		<h4 class="section_title"><span><?php esc_html_e( 'Top sellers', 'licenses-manager-for-woocommerce' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
			<?php
			$top_sellers = $this->get_order_report_data(
				array(
					'data'         => array(
						'_product_id' => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => '',
							'name'            => 'product_id',
						),
						'_qty'        => array(
							'type'            => 'order_item_meta',
							'order_item_type' => 'line_item',
							'function'        => 'SUM',
							'name'            => 'order_item_qty',
						),
					),
					'order_by'     => 'order_item_qty DESC',
					'group_by'     => 'product_id',
					'limit'        => 12,
					'query_type'   => 'get_results',
					'filter_range' => true,
					'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
				)
			);

			if ( $top_sellers ) {
				foreach ( $top_sellers as $product ) {
					?>
						<tr class="
						<?php
						//  phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
						echo ( in_array( $product->product_id, $this->product_ids, false ) ? 'active' : '' );
						?>
						">
							<td class="count"><?php echo esc_html( $product->order_item_qty ); ?></td>
							<td class="name">
								<a href="<?php echo esc_url( add_query_arg( 'product_ids', $product->product_id ) ); ?>"><?php echo esc_html( get_the_title( $product->product_id ) ); ?></a>
							</td>
							<td class="sparkline">
								<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $this->sales_sparkline( $product->product_id, 7, 'count' );
								?>
							</td>
						</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="3"><?php echo esc_html__( 'No products found in range', 'licenses-manager-for-woocommerce' ); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
		</div>
		<h4 class="section_title"><span><?php esc_html_e( 'Top freebies', 'licenses-manager-for-woocommerce' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_freebies = $this->get_order_report_data(
					array(
						'data'         => array(
							'_product_id' => array(
								'type'            => 'order_item_meta',
								'order_item_type' => 'line_item',
								'function'        => '',
								'name'            => 'product_id',
							),
							'_qty'        => array(
								'type'            => 'order_item_meta',
								'order_item_type' => 'line_item',
								'function'        => 'SUM',
								'name'            => 'order_item_qty',
							),
						),
						'where_meta'   => array(
							array(
								'type'       => 'order_item_meta',
								// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
								'meta_key'   => '_line_subtotal',
								// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
								'meta_value' => '0',
								'operator'   => '=',
							),
						),
						'order_by'     => 'order_item_qty DESC',
						'group_by'     => 'product_id',
						'limit'        => 12,
						'query_type'   => 'get_results',
						'filter_range' => true,
					)
				);

				if ( $top_freebies ) {
					foreach ( $top_freebies as $product ) {
						?>
						<tr class="
						<?php
						//  phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
						echo ( in_array( $product->product_id, $this->product_ids, false ) ? 'active' : '' );
						?>
						">
							<td class="count">
								<?php
									echo esc_html( $product->order_item_qty );
								?>
							</td>
							<td class="name"><a href="<?php echo esc_url( add_query_arg( 'product_ids', $product->product_id ) ); ?>"><?php echo esc_html( get_the_title( $product->product_id ) ); ?></a></td>
							<td class="sparkline">
								<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $this->sales_sparkline( $product->product_id, 7, 'count' );
								?>
							</td>
						</tr>
						<?php
					}
				} else {
					?>
						<tr>
							<td colspan="3"><?php echo esc_html__( 'No products found in range', 'licenses-manager-for-woocommerce' ); ?></td>
						</tr>
					<?php
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php esc_html_e( 'Top earners', 'licenses-manager-for-woocommerce' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_earners = $this->get_order_report_data(
					array(
						'data'         => array(
							'_product_id' => array(
								'type'            => 'order_item_meta',
								'order_item_type' => 'line_item',
								'function'        => '',
								'name'            => 'product_id',
							),
							'_line_total' => array(
								'type'            => 'order_item_meta',
								'order_item_type' => 'line_item',
								'function'        => 'SUM',
								'name'            => 'order_item_total',
							),
						),
						'order_by'     => 'order_item_total DESC',
						'group_by'     => 'product_id',
						'limit'        => 12,
						'query_type'   => 'get_results',
						'filter_range' => true,
						'order_status' => array( 'completed', 'processing', 'on-hold', 'refunded' ),
					)
				);

				if ( $top_earners ) {
					foreach ( $top_earners as $product ) {
						?>
						<tr class="
						<?php
						//  phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
						echo ( in_array( $product->product_id, $this->product_ids, false ) ? 'active' : '' );
						?>
						">
							<td class="count">
								<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo wc_price( $product->order_item_total );
								?>
							</td>
							<td class="name">
								<a href="<?php echo esc_url( add_query_arg( 'product_ids', $product->product_id ) ); ?>"><?php echo esc_html( get_the_title( $product->product_id ) ); ?></a>
							</td>
							<td class="sparkline">
								<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $this->sales_sparkline( $product->product_id, 7, 'sales' );
								?>
							</td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="3"><?php echo esc_html__( 'No products found in range', 'licenses-manager-for-woocommerce' ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<script>
			jQuery('.section_title').click(function () {
				var next_section = jQuery(this).next('.section');

				if (jQuery(next_section).is(':visible'))
					return false;

				jQuery('.section:visible').slideUp();
				jQuery('.section_title').removeClass('open');
				jQuery(this).addClass('open').next('.section').slideDown();

				return false;
			});
			jQuery('.section').slideUp(100, function () {
				<?php if ( empty( $this->product_ids ) ) : ?>
					jQuery('.section_title:eq(1)').click();
				<?php endif; ?>
			});
		</script>
			<?php
	}

	public function get_export_button() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_range = ! empty( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '7day';
		?>
			<a
				href="#"
				download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_html( date_i18n( 'Y-m-d', current_time( 'mysql' ) ) ); ?>.csv"
				class="export_csv"
				data-export="chart"
				data-taxes="<?php esc_attr_e( 'Date', 'licenses-manager-for-woocommerce' ); ?>"
				data-groupby="<?php echo esc_attr($this->chart_groupby); ?>"<?php // @codingStandardsIgnoreLine                                              ?>
				>
				<?php esc_html_e( 'Export CSV', 'licenses-manager-for-woocommerce' ); ?>
			</a>
			<?php
	}

	public function get_main_chart() {

		global $wp_locale;

		if ( empty( $this->product_ids ) ) {
			?>
				<div class="chart-container">
					<p class="chart-prompt"><?php esc_html_e( 'Choose a product to view stats', 'licenses-manager-for-woocommerce' ); ?></p>
				</div>
				<?php
		} else {
			$data = array(
				'licenses_created'     => $this->prepare_chart_data( $this->report_data->licenses, 'license_created', '', $this->chart_interval, $this->start_date, $this->chart_groupby ),
				'licenses_activations' => $this->prepare_chart_data( $this->report_data->activations, 'activation_created', '', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			);

			$data = apply_filters( 'woocommerce_admin_report_chart_data', $data );

			$chart_data = wp_json_encode(
				array(
					'licenses_created'     => array_values( $data['licenses_created'] ),
					'licenses_activations' => array_map( array( $this, 'round_chart_totals' ), array_values( $data['licenses_activations'] ) ),
				)
			);
			?>
				<div class="chart-container">
					<div class="chart-placeholder main"></div>
				</div>
				<script>
					var main_chart;

					jQuery(function () {
						var order_data = JSON.parse(decodeURIComponent('<?php echo rawurlencode( $chart_data ); ?>'));

						var drawGraph = function (highlight) {

						var series = [
							{
							label: "<?php echo esc_js( esc_html__( 'Product licenses', 'licenses-manager-for-woocommerce' ) ); ?>",
							data: order_data.licenses_created,
							color: '<?php echo esc_attr( $this->chart_colours['item_count'] ); ?>',
							bars: {fillColor: '<?php echo esc_attr( $this->chart_colours['item_count'] ); ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo esc_attr( $this->barwidth ); ?> * 0.5, align: 'center'},
							shadowSize: 0,
							hoverable: false
							},
							{
							label: "<?php echo esc_js( esc_html__( 'Product activations', 'licenses-manager-for-woocommerce' ) ); ?>",
							data: order_data.licenses_activations,
							yaxis: 2,
							color: '<?php echo esc_attr( $this->chart_colours['sales_amount'] ); ?>',
							points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 4, fill: false},
							shadowSize: 0,
							}
						];

						if (highlight !== 'undefined' && series[ highlight ]) {
							highlight_series = series[ highlight ];

							highlight_series.color = '#9c5d90';

							if (highlight_series.bars)
							highlight_series.bars.fillColor = '#9c5d90';

							if (highlight_series.lines) {
							highlight_series.lines.lineWidth = 5;
							}
						}

						main_chart = jQuery.plot(
								jQuery('.chart-placeholder.main'),
								series,
								{
									legend: {
									show: false
									},
									grid: {
									color: '#aaa',
									borderColor: 'transparent',
									borderWidth: 0,
									hoverable: true
									},
									xaxes: [{
										color: '#aaa',
										position: "bottom",
										tickColor: 'transparent',
										mode: "time",
										timeformat: "<?php echo ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b'; ?>",
										monthNames: JSON.parse(decodeURIComponent('<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>')),
										tickLength: 1,
										minTickSize: [1, "<?php echo esc_attr( $this->chart_groupby ); ?>"],
										font: {
										color: "#aaa"
										}
									}],
									yaxes: [
									{
										min: 0,
										minTickSize: 1,
										tickDecimals: 0,
										color: '#ecf0f1',
										font: {color: "#aaa"}
									},
									{
										position: "right",
										min: 0,
										tickDecimals: 2,
										alignTicksWithAxis: 1,
										color: 'transparent',
										font: {color: "#aaa"}
									}
									],
								}
						);

						jQuery('.chart-placeholder').resize();
						}

						drawGraph();

						jQuery('.highlight_series').hover(
								function () {
								drawGraph(jQuery(this).data('series'));
								},
								function () {
								drawGraph();
								}
						);
					});
				</script>
				<?php
		}
	}
}


