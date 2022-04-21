<?php

namespace MEC\SingleBuilder\Widgets\EventPublicDownload;

use MEC\Base;
use MEC\SingleBuilder\Widgets\WidgetBase;

class EventPublicDownload extends WidgetBase {

	/**
	 *  Get HTML Output
	 *
	 * @param int $event_id
	 * @param array $atts
	 *
	 * @return string
	 */
	public function output( $event_id = 0, $atts = array() ){

		if( !$event_id ){

			$event_id = $this->get_event_id();
		}

		if(!$event_id){
			return '';
		}

		$settings = $this->settings;
		$events_detail = $this->get_event_detail($event_id);

		$single = new \MEC_skin_single();
		$html = $single->display_public_download_module( $events_detail );

		return $html;
	}
}
