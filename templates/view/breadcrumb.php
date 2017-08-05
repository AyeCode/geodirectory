<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! empty( $content ) ) {

    echo $wrap_start;

    echo $content;

    echo $wrap_end;

}
