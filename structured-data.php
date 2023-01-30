<?php
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_23598c8a800dc',
	'title' => 'WordAutoLinker',
	'fields' => array(
		array(
			'key' => 'field_60035da928786',
			'label' => 'Keyword Principale',
			'name' => 'main_keyword',
			'type' => 'text',
			'instructions' => 'Inserisci qui la keyword (anche più parole) principale di questo post, tutti i post che conterranno questa parola saranno automaticamente linkati a questo articolo.
ATTENZIONE: inseriscila solo se è realmente rilevante, ad esempio se è la recensione di un modello unico puoi inserire il nome del modello, oppure se è la scheda di un luogo unico al mondo, altrimenti ignoralo.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '70',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '', 
		),
		/*
		array(
			'key' => 'field_601ad3a1c9da1',
			'label' => 'Istanze massime',
			'name' => 'wdlnk_istances',
			'type' => 'number',
			'instructions' => 'Puoi limitare il numero di volte in cui questa keyword viene linkata negli altri post. Di default è settata a 3.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '30',
				'class' => '',
				'id' => '',
			),
			'default_value' => 3,
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'min' => 1,
			'max' => 99,
			'step' => 1,
		),
		*/
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'post',
			),
			array(
				'param' => 'current_user_role',
				'operator' => '==',
				'value' => 'administrator',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'point',
			),
			array(
				'param' => 'current_user_role',
				'operator' => '==',
				'value' => 'administrator',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
			),
			array(
				'param' => 'current_user_role',
				'operator' => '==',
				'value' => 'administrator',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'side',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));

endif;