<?php
/**
* PageCarton Page Generator
*
* LICENSE
*
* @category PageCarton
* @package /posts
* @generated Ayoola_Page_Editor_Layout
* @copyright  Copyright (c) PageCarton. (http://www.PageCarton.com)
* @license    http://www.PageCarton.com/license.txt
* @version $Id: posts.php	Sunday 6th of September 2020 02:21:22 AM	ayoola@ayoo.la $ 
*/
//	Page Include Content

							if( Ayoola_Loader::loadClass( 'Ayoola_Page_Editor_Text' ) )
							{
								
$_15bf3081bb0016c6639cdabe4610d211 = new Ayoola_Page_Editor_Text( array (
  'editable' => '
<div class="py-5 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <span>Category</span>
            <h3>Sports</h3>
            <p>Category description here.. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquam error eius quo, officiis non maxime quos reiciendis perferendis doloremque maiores!</p>
          </div>
        </div>
      </div>
    </div>

',
  'preserved_content' => '<widget parameters=\'{ "class": "Application_Category_View", "pc_module_url_values_category_offset": 0, "pc_module_url_values_request_fallback": true, "allow_dynamic_category_selection": true }\'>
  <div class="py-5 bg-light">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <span>Category</span>
          <h3>{{{category_label}}}</h3>
          <p>{{{category_description}}}</p>
        </div>
      </div>
    </div>
  </div>
</widget>
',
  'url_prefix' => '',
  'widget_options' => 
  array (
    0 => 'preserve_content',
  ),
  'pagewidget_id' => '0-1599312165-7',
  'content' => '<widget parameters=\'{ "class": "Application_Category_View", "pc_module_url_values_category_offset": 0, "pc_module_url_values_request_fallback": true, "allow_dynamic_category_selection": true }\'>
  <div class="py-5 bg-light">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <span>Category</span>
          <h3>{{{category_label}}}</h3>
          <p>{{{category_description}}}</p>
        </div>
      </div>
    </div>
  </div>
</widget>
',
) );

							}
							else
							{
								
$_15bf3081bb0016c6639cdabe4610d211 = null;

							}
							
							if( Ayoola_Loader::loadClass( 'Ayoola_Page_Editor_Text' ) )
							{
								
$_4a5d5d053d0f41ae64c7b5cb334b00ea = new Ayoola_Page_Editor_Text( array (
  'editable' => '
<div class="site-section bg-white">
      <div class="container">
        <div class="row">
            <include href="/layout/pc_layout_miniblog/list-post.html"></include>
</div>
        <div class="row text-center pt-5 border-top">
          <div class="col-md-12">
            <div class="custom-pagination">
              <span>1</span>
              <a href="#">2</a>
              <a href="#">3</a>
              <a href="#">4</a>
              <span>...</span>
              <a href="#">15</a>
            </div>
          </div>
      </div>
    </div>
  </div>

',
  'preserved_content' => '<div class="site-section bg-white">
    <div class="container">
        <include href="/layout/pc_layout_miniblog/list-post.html">
    </div>
</div>',
  'url_prefix' => '',
  'widget_options' => 
  array (
    0 => 'preserve_content',
  ),
  'pagewidget_id' => '0-1599316816-14',
  'includes' => 
  array (
    '/layout/pc_layout_miniblog/list-post' => '<include href="/layout/pc_layout_miniblog/list-post.html">',
  ),
  'content' => '<div class="site-section bg-white">
    <div class="container">
        <include href="/layout/pc_layout_miniblog/list-post.html">
    </div>
</div>',
) );

							}
							else
							{
								
$_4a5d5d053d0f41ae64c7b5cb334b00ea = null;

							}
							