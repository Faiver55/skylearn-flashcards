<?php
/**
 * Template for displaying a flashcard set
 *
 * This template can be overridden by copying it to yourtheme/skylearn-flashcards/flashcard-set.php
 *
 * @since   1.0.0
 * @package SkyLearn_Flashcards
 * @var     array  $flashcard_set Flashcard set data
 * @var     array  $settings      Display settings  
 * @var     string $container_id   Container ID
 * @var     int    $set_id         Set ID
 * @var     string $theme          Theme name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Use renderer to generate the HTML
require_once SKYLEARN_FLASHCARDS_PATH . 'includes/frontend/class-renderer.php';
$renderer = new SkyLearn_Flashcards_Renderer( 'skylearn-flashcards', SKYLEARN_FLASHCARDS_VERSION );

echo $renderer->render_flashcard_set( $flashcard_set, $settings, $container_id );