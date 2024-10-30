<?php

namespace QuadLayers\WLM\Backend\Controller\Reports\View;

use QuadLayers\WLM\Models\Activation\Mapper as Model_Activation_Mapper;
use QuadLayers\WLM\Models\License\Mapper as Model_License_Mapper;

class Licenses_By_Date extends \WC_Admin_Report {

	public $chart_colours = array();
	private $report_data;

	public function get_report_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_data();
		}
		return $this->report_data;
	}

	public function get_activations_report_data( $status = 'active' ) {

		$date_start = $this->start_date;
		$date_end   = strtotime( '+1 DAY', $this->end_date );

		$activations = Model_Activation_Mapper::get(
			array(
				'activation_status'     => $status,
				'activation_date_start' => $date_start,
				'activation_date_end'   => $date_end,
			)
		);

		return (array) $activations;
	}

	public function get_licenses_report_data() {

		$date_start = $this->start_date;
		$date_end   = strtotime( '+1 DAY', $this->end_date );

		$licenses = Model_License_Mapper::get(
			array(
				'license_date_start' => $date_start,
				'license_date_end'   => $date_end,
			)
		);

		return (array) $licenses;
	}

	private function query_report_data() {

		$this->report_data = new \stdClass();

		$this->report_data->licenses      = $this->get_licenses_report_data();
		$this->report_data->activations   = $this->get_activations_report_data( 'active' );
		$this->report_data->deactivations = $this->get_activations_report_data( 'inactive' );
		// $this->report_data->average_total_licenses = (count($this->report_data->licenses) / ( $this->chart_interval + 1 ));
		$this->report_data->averagel_activations = ( count( $this->report_data->activations ) / ( $this->chart_interval + 1 ) );

		$this->report_data = apply_filters( 'qlwlm_backend_report_data', $this->report_data );
	}

	public function get_chart_legend() {

		$legend = array();

		$data = $this->get_report_data();

		$legend[] = array(
			'title'            => sprintf( esc_html__( '%s total activations in this period', 'licenses-manager-for-woocommerce' ), '<strong>' . count( $this->report_data->activations ) . '</strong>' ),
			'placeholder'      => esc_html__( 'This is the sum of the order totals after any refunds and including shipping and taxes', 'licenses-manager-for-woocommerce' ),
			'color'            => $this->chart_colours['licenses_activations'],
			'highlight_series' => 2,
		);

		if ( $data->averagel_activations > 0 ) {
			$legend[] = array(
				'title'            => sprintf( esc_html__( '%s average activations', 'licenses-manager-for-woocommerce' ), '<strong>' . wc_format_decimal( $data->averagel_activations, 2 ) . '</strong>' ),
				'color'            => $this->chart_colours['average_activations'],
				'highlight_series' => 1,
			);
		}

		$legend[] = array(
			'title'            => sprintf( esc_html__( '%s total license created', 'licenses-manager-for-woocommerce' ), '<strong>' . count( $this->report_data->licenses ) . '</strong>' ),
			'color'            => $this->chart_colours['licenses_created'],
			'highlight_series' => 0,
		);
		/*
			$legend[] = array(
				'title'            => sprintf( esc_html__( '%s average license created', 'licenses-manager-for-woocommerce' ), '<strong>' . wc_format_decimal( $data->average_total_licenses, 2 ) . '</strong>' ),
				'color'            => $this->chart_colours['coupon_amount'],
				'highlight_series' => 4,
			);
		*/
		$legend[] = array(
			'title'            => sprintf( esc_html__( '%s total deactivations', 'licenses-manager-for-woocommerce' ), '<strong>' . intval( $this->report_data->deactivations ) . '</strong>' ),
			'color'            => $this->chart_colours['licenses_deactivations'],
			'highlight_series' => 3,
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
			'licenses_created'       => '#8fdece', // '#f1c40f',//'#ecf0f1',
			'licenses_activations'   => '#1abc9c', // '#5cc488',
			'licenses_deactivations' => '#e74c3c',
			'average_activations'    => '#f1c40f',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_range = ! empty( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '7day';

		//  phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ), false ) ) {
			$current_range = '7day';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	public function get_export_button() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_range = ! empty( $_GET['range'] ) ? sanitize_key( wp_unslash( $_GET['range'] ) ) : '7day';
		?>
			<a
				href="#"
				download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_attr( date_i18n( 'Y-m-d', current_time( 'mysql' ) ) ); ?>.csv"
				class="export_csv"
				data-export="chart"
				data-xaxes="<?php esc_attr_e( 'Date', 'licenses-manager-for-woocommerce' ); ?>"
				data-exclude_series="2"
				data-groupby="<?php echo esc_attr( $this->chart_groupby ); ?>"
				>
				<?php esc_html_e( 'Export CSV', 'licenses-manager-for-woocommerce' ); ?>
			</a>
			<?php
	}

	private function round_chart_totals( $amount ) {
		if ( is_array( $amount ) ) {
			return array( $amount[0], wc_format_decimal( $amount[1], 0 ) );
		} else {
			return wc_format_decimal( $amount, 0 );
		}
	}

	public function get_main_chart() {

		global $wp_locale;

		$data = array(
			'licenses_created'       => $this->prepare_chart_data( $this->report_data->licenses, 'license_created', '', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			'licenses_activations'   => $this->prepare_chart_data( $this->report_data->activations, 'activation_created', '', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			'licenses_deactivations' => $this->prepare_chart_data( $this->report_data->deactivations, 'activation_created', '', $this->chart_interval, $this->start_date, $this->chart_groupby ),
		);

		$data = apply_filters( 'woocommerce_admin_report_chart_data', $data );

		$chart_data = wp_json_encode(
			array(
				'licenses_created'       => array_values( $data['licenses_created'] ),
				'licenses_activations'   => array_map( array( $this, 'round_chart_totals' ), array_values( $data['licenses_activations'] ) ),
				'licenses_deactivations' => array_map( array( $this, 'round_chart_totals' ), array_values( $data['licenses_deactivations'] ) ),
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
						label: "<?php echo esc_js( esc_html__( 'Number of items sold', 'licenses-manager-for-woocommerce' ) ); ?>",
						data: order_data.licenses_created,
						color: '<?php echo esc_attr( $this->chart_colours['licenses_created'] ); ?>',
						bars: {fillColor: '<?php echo esc_attr( $this->chart_colours['licenses_created'] ); ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo esc_attr( $this->barwidth ); ?> * 0.5, align: 'center'},
						shadowSize: 0,
						hoverable: false
					},
					{
						label: "<?php echo esc_js( esc_html__( 'Average activations', 'licenses-manager-for-woocommerce' ) ); ?>",
						data: [[<?php echo esc_attr( min( array_keys( $data['licenses_activations'] ) ) ); ?>, <?php echo esc_attr( $this->report_data->averagel_activations ); ?>], [<?php echo esc_attr( max( array_keys( $data['licenses_activations'] ) ) ); ?>, <?php echo esc_attr( $this->report_data->averagel_activations ); ?>]],
						yaxis: 2,
						color: '<?php echo esc_attr( $this->chart_colours['average_activations'] ); ?>',
						points: {show: false},
						lines: {show: true, lineWidth: 2, fill: false},
						shadowSize: 0,
						hoverable: false
					},
					{
						label: "<?php echo esc_js( esc_html__( 'Licenses activations', 'licenses-manager-for-woocommerce' ) ); ?>",
						data: order_data.licenses_activations,
						yaxis: 2,
						color: '<?php echo esc_attr( $this->chart_colours['licenses_activations'] ); ?>',
						points: {show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true},
						lines: {show: true, lineWidth: 5, fill: false},
						shadowSize: 0,
					},
					{
						label: "<?php echo esc_js( esc_html__( 'Licenses deactivations', 'licenses-manager-for-woocommerce' ) ); ?>",
						data: order_data.licenses_deactivations,
						yaxis: 2,
						color: '<?php echo esc_attr( $this->chart_colours['licenses_deactivations'] ); ?>',
						points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
						lines: {show: true, lineWidth: 2, fill: false},
						shadowSize: 0,
					},
					];

					if (highlight !== 'undefined' && series[ highlight ]) {
					highlight_series = series[ highlight ];
					highlight_series.color = '#9c5d90';
					if (highlight_series.bars) {
						highlight_series.bars.fillColor = '#9c5d90';
					}

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
								color: '#d4d9dc',
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
