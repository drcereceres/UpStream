<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $doctype;
?>

<!DOCTYPE html>
<html <?php language_attributes( $doctype ); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title><?php wp_title( '|', true, 'right' ) . bloginfo( 'name' ); ?></title>

    <?php wp_head(); ?>
    <?php do_action( 'upstream_head' ); ?>
</head>
<body class="nav-md upstream-front-end">
<div class="container body">
    <div class="main_container">
