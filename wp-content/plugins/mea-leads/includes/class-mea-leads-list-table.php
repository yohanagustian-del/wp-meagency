<?php
/**
 * Tabel daftar lead di wp-admin.
 *
 * @package MEA_Leads
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Daftar lead dengan filter status, sumber, dan rentang tanggal.
 */
class MEA_Leads_List_Table extends WP_List_Table {

	/**
	 * Filter aktif.
	 *
	 * @var array<string, string>
	 */
	public $filters = array();

	/**
	 * Siapkan tabel.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'lead',
				'plural'   => 'leads',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Kolom.
	 *
	 * @return array<string, string>
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox">',
			'submitted_at' => __( 'Tanggal', 'mea-leads' ),
			'nama'         => __( 'Nama', 'mea-leads' ),
			'whatsapp'     => __( 'WhatsApp', 'mea-leads' ),
			'omzet'        => __( 'Omzet', 'mea-leads' ),
			'layanan'      => __( 'Layanan', 'mea-leads' ),
			'sumber_form'  => __( 'Sumber form', 'mea-leads' ),
			'halaman_asal' => __( 'Halaman asal', 'mea-leads' ),
			'status'       => __( 'Status', 'mea-leads' ),
			'percobaan'    => __( 'Percobaan', 'mea-leads' ),
			'cdps_lead_id' => __( 'ID lead CDPS', 'mea-leads' ),
		);
	}

	/**
	 * Aksi massal.
	 *
	 * @return array<string, string>
	 */
	protected function get_bulk_actions() {
		return array( 'kirim-ulang' => __( 'Kirim ulang', 'mea-leads' ) );
	}

	/**
	 * Ambil data sesuai filter dan halaman.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$per_page = 20;
		$result   = mea_leads_query( $this->filters, $per_page, $this->get_pagenum() );

		$this->items           = $result['rows'];
		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->set_pagination_args(
			array(
				'total_items' => $result['total'],
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Kotak centang.
	 *
	 * @param object $item Baris.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="lead[]" value="%d">', (int) $item->id );
	}

	/**
	 * Tanggal (WIB) + aksi baris.
	 *
	 * @param object $item Baris.
	 * @return string
	 */
	protected function column_submitted_at( $item ) {
		$local   = ( new DateTimeImmutable( $item->submitted_at, new DateTimeZone( 'UTC' ) ) )->setTimezone( new DateTimeZone( 'Asia/Jakarta' ) );
		$detail  = add_query_arg(
			array(
				'page' => 'mea-leads',
				'lead' => (int) $item->id,
			),
			admin_url( 'admin.php' )
		);
		$actions = array( 'riwayat' => '<a href="' . esc_url( $detail ) . '">' . esc_html__( 'Riwayat', 'mea-leads' ) . '</a>' );
		if ( 'terkirim' !== $item->status && '' !== (string) $item->body ) {
			$url                    = wp_nonce_url( admin_url( 'admin-post.php?action=mea_leads_kirim_ulang&lead=' . (int) $item->id ), 'mea_leads_kirim_ulang' );
			$actions['kirim-ulang'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Kirim ulang', 'mea-leads' ) . '</a>';
		}
		return esc_html( $local->format( 'd-m-Y H:i' ) . ' WIB' ) . $this->row_actions( $actions );
	}

	/**
	 * Status dengan label.
	 *
	 * @param object $item Baris.
	 * @return string
	 */
	protected function column_status( $item ) {
		$label = esc_html( $item->status );
		if ( $item->dianonimkan_at ) {
			$label .= '<br><span class="description">' . esc_html__( 'data pribadi dihapus', 'mea-leads' ) . '</span>';
		} elseif ( 'menunggu' === $item->status && $item->berikutnya_at ) {
			$next   = ( new DateTimeImmutable( $item->berikutnya_at, new DateTimeZone( 'UTC' ) ) )->setTimezone( new DateTimeZone( 'Asia/Jakarta' ) );
			$label .= '<br><span class="description">' . esc_html( sprintf( /* translators: %s: waktu. */ __( 'coba lagi %s', 'mea-leads' ), $next->format( 'd-m H:i' ) ) ) . '</span>';
		}
		return $label;
	}

	/**
	 * Kolom lain.
	 *
	 * @param object $item        Baris.
	 * @param string $column_name Nama kolom.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		$value = (string) ( $item->$column_name ?? '' );
		if ( 'omzet' === $column_name ) {
			$value = mea_leads_omzet_options()[ $value ] ?? $value;
		}
		return '' === $value ? '—' : esc_html( $value );
	}

	/**
	 * Pesan saat kosong.
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'Belum ada lead.', 'mea-leads' );
	}
}
