<?php

/*
Name: Page Collections
Author: SeanChDavis
Description: Create collections of pages that can be displayed on the frontend.
Version: 0.2
Requires: 0.1
Class: SCHD_Page_Collections
Docs:
License: MIT
License URI: https://opensource.org/licenses/mit
Copyright (c) 2025 SeanChDavis
*/

class SCHD_Page_Collections extends PM_Plugin
{
    public $title = 'Page Collection';
    public $name = 'Page Collection';
    public $type = 'box';

    /**
     * Creates 'Page Collections' content type in the 'theme' environment
     *
     * Creating a new Page Collection is similar to creating a new page.
     * This also creates a new Page Collection template in the template editor.
     * The template controls how the collection is displayed on the frontend.
     *
     * @return array The content types array with 'page-collection' type.
     */
    public function content_types(): array
    {
        return array(
            'page-collection' => array(
                'name'        => $this->title,
                'environment' => array('theme'),
                'url'         => true,
                'fields'      => array('title', 'page-url', 'status', 'content'),
                'groups'      => array(
                    'page-url' => array('slug', 'arrow', 'url')
                ),
            )
        );
    }

    /**
     * Returns site options for Page Collections
     *
     * This method creates a text field for each page-collection (pm_content.type).
     * The text field takes comma-separated page IDs to include in the collection.
     *
     * @return array The site options array with fields for each page collection.
     */
    public function site_options(): array
    {
        global $motor;

        $fields = array();

        // Only show options if there are page collections
        $collections = $this->get_page_collections_list();
        if (empty($collections)) {
            $fields['no-collections'] = array(
                'type' => 'custom',
                'html' => '<div class="callout note" style="max-width:720px"><p style="margin-bottom: .25rem;">Please create a new Page Collection from the <a href="'.$motor->admin_url('content').'">Content Types</a> page to get started.</p></div>',
            );
        } else { // Sweet, we have collections!

            // Begin building the fields array
            $fields = [
                'description' => array(
                    'type' => 'custom',
                    'html' => '<div style="max-width:720px"><p style="margin-bottom: 1.25rem;">Each field represents a Page Collection. To include pages in a collection, enter comma-separated page IDs. For example, to include pages with IDs 3 and 5, enter <code>3,5</code>.</p><p>For reference, a list of live pages and their IDs are at the bottom of this page. To create more Page Collections, visit the <a href="'.$motor->admin_url('content').'">Content Types</a> page. Control the display of your Page Collections from the <a href="'.$motor->admin_url('theme/editor').'">Template Editor</a>.</p></div>',
                )
            ];

            // Create a text field for each page-collection in pm_content
            foreach ($collections as $collection) {
                $fields[$collection['id']] = array(
                    'type'        => 'text',
                    'label'       => $collection['title'],
                    'tooltip'     => 'Enter comma-separated page IDs to include in this collection.',
                    'placeholder' => '3,5',
                    'width'       => 'large',
                    'description' => 'Page Collection ID: '.$collection['id'].
                                     ' | <a href="'.$motor->url($collection['slug']).'" target="_blank">View Page Collection</a>',
                );
            }

            // As a helper, display a list of all pages with their IDs
            $fields['page-collection-list'] = array(
                'type' => 'custom',
                'html' => '<div id="page-reference" style="max-width:664px;margin-top:2rem;">'.
                          '<strong>Live Page Reference:</strong>'.
                          '<ul style="margin:0;padding:0;">'.
                          implode('', array_map(function ($label) {
                              return '<li style="margin:0;">'.$label.'</li>';
                          }, $this->get_pages_list())).
                          '</ul>'.
                          '</div>',
            );
        }

        return $fields;
    }

    public function html_options(): array
    {
        global $motor;
        $default_html                     = $motor->options->html();
        $default_html['class']['tooltip'] = 'If you would like to add to the existing <code>page-collection</code> wrapping class, you can do so here. This is useful for adding custom styles to the collection wrapper.';
        $default_html['id']['tooltip']    = 'If you would like to add an ID to the collection wrapper, you can do so here. Note that the wrapper already has a <code>page-collection</code> class.';

        $options = array(
            "list_markup"    => array(
                "type"    => "select",
                "label"   => "List Markup",
                "tooltip" => "Determine the HTML markup for collection.",
                "options" => array(
                    "ul"      => "Unordered &lt;li&gt; Items",
                    "ol"      => "Ordered &lt;li&gt; Items",
                    "div"     => "&lt;div&gt; Containers",
                    "article" => "&lt;article&gt; Containers",
                ),
            ),
            "title_tag"      => array(
                "type"    => "select",
                "label"   => "Item Title Tags",
                "tooltip" => "Select the HTML tag for item titles.",
                "options" => array(
                    "h1"   => "h1",
                    "h2"   => "h2",
                    "h3"   => "h3",
                    "h4"   => "h4",
                    "h5"   => "h5",
                    "h6"   => "h6",
                    "span" => "span",
                ),
            ),
            "inline_styles"  => array(
                "type"    => "checkbox",
                "label"   => "Inline Styles",
                "tooltip" => "Make small, convenient adjustments to the collection's appearance using inline styles.",
                "options" => array(
                    "align_titles_left"  => "Force left alignment of titles",
                    "collection_spacing" => "Add space above and below collection",
                    "item_spacing"       => "Add spacing between items",
                    "read_more_button"   => "Style 'Read More' link as 'Basic' button",
                )
            ),
            "link_title"     => array(
                "type"    => "checkbox",
                "label"   => "Link Item Titles",
                "tooltip" => "If checked, item titles will be linked to their respective pages.",
                "options" => array(
                    "on" => "Link item titles to pages"
                )
            ),
            "show_content"   => array(
                "type"    => "checkbox",
                "label"   => "Show Content",
                "tooltip" => "If checked, a truncated version of the page content will be displayed.",
                "options" => array(
                    "on" => "Show truncated page content"
                )
            ),
            "content_length" => array(
                "type"    => "text",
                "label"   => "Content Length (word count)",
                "tooltip" => "Number of words to display from the page content.",
                "width"   => "small",
            ),
            "read_more"      => array(
                "type"    => "text",
                "label"   => "'Read More' Link Text",
                "tooltip" => "Add a 'Read More' link if truncated content is shown. Leave blank (or disable 'Show Content') for no link.",
                "width"   => "medium",
            ),
            "class"          => array(
                "type"    => "text",
                "label"   => "Custom Class",
                "tooltip" => "Add a custom class to the page collection wrapper for additional styling.",
                "width"   => "medium",
            ),
        );

        return array_merge($options, $default_html);
    }

    /**
     * Retrieves a list of all Page Collection entries in pm_content
     *
     * @return array
     */
    public function get_page_collections_list(): array
    {
        global $motor;

        return $motor->content->get_where(
            array(
                'type'   => 'page-collection',
                'status' => 'live',
            )
        );
    }

    /**
     * Retrieves a list of all Pages in pm_content
     *
     * @return array
     */
    public function get_pages_list(): array
    {
        global $motor;

        // Get live pages from pm_content
        $all_pages = $motor->content->get_where(
            array(
                'type'   => 'page',
                'status' => 'live',
            )
        );

        // Build an array of page IDs to list them in "ID - Title" format
        $page_ids = array();
        foreach ($all_pages as $page) {
            $page_ids[$page['id']] = $page['id'].' - '.$page['title'];
        }

        return $page_ids;
    }

    /**
     * Outputs HTML for the page collection on the frontend
     *
     * This method checks if the current page is a page collection.
     * If so, it retrieves the associated pages and displays them.
     *
     * @param  int  $depth  The depth for indentation in the HTML output.
     */
    public function html($depth = 0): void
    {
        global $motor;
        $tab = str_repeat("\t", $depth);

        // Get saved collections from site options
        // Example data: {"17":"5,3","20":"4,3,5"} where 17 and 20 are collection IDs
        $collections = $motor->options->option('SCHD_Page_Collections', array());

        // bail if no collections are defined
        if (empty($collections)) {
            return;
        }

        // Get the current page ID
        $current_page_id = $motor->page->content['id'];

        // If the current page is a page collection, display its collected pages
        if (isset($collections[$current_page_id])) {
            $collected_page_ids = array_unique(explode(',', $collections[$current_page_id]));

            // Build the markup based on the selected list type
            $list_tag      = $this->box_options['list_markup'] ?? 'ul';
            $list_type_tag = '';
            if (in_array($list_tag, array('ul', 'ol'))) {
                $list_type_tag = $list_tag;
            }
            $item_tag  = ($list_tag === 'div' || $list_tag === 'article') ? $list_tag : 'li';
            $title_tag = $this->box_options['title_tag'] ?? 'h4';

            $align_left = $collection_margin = $item_gap = $link_style = '';
            if ( ! empty($this->box_options['inline_styles'])) {
                $force_align        = isset($this->box_options['inline_styles']['align_titles_left']);
                $align_left         = $force_align ? ' style="text-align:left;"' : '';
                $collection_spacing = isset($this->box_options['inline_styles']['collection_spacing']);
                $collection_margin  = $collection_spacing ? ' style="margin:3rem 0;"' : '';
                $item_spacing       = isset($this->box_options['inline_styles']['item_spacing']);
                $item_gap           = $item_spacing ? ' style="margin-bottom:2.75rem;"' : '';
                $read_more_button   = isset($this->box_options['inline_styles']['read_more_button']);
                $link_style         = $read_more_button ? ' class="button basic"' : '';
            }

            $title_link      = ! empty($this->box_options['link_title']) && $this->box_options['link_title']['on'];
            $show_content    = ! empty($this->box_options['show_content']) && $this->box_options['show_content']['on'];
            $content_length  = ! empty($this->box_options['content_length']) ? (int) $this->box_options['content_length'] : 20;
            $read_more_text  = ! empty($this->box_options['read_more']) ? $motor->text($this->box_options['read_more'],
                'no-html') : '';
            $container_class = ! empty($this->box_options['class']) ? ' '.$motor->text($this->box_options['class'],
                    'no-html') : '';
            $container_id    = ! empty($this->box_options['id']) ? ' id="'.$motor->text($this->box_options['id'],
                    'no-html').'"' : '';

            if ( ! empty($collected_page_ids)) {
                $count = count($collected_page_ids);
                $i     = 0;

                // Wrapper for the entire collection
                echo "$tab<div class=\"page-collection".$container_class."\" ".$collection_margin." ".$container_id.">\n";

                echo $list_type_tag ? "$tab\t<".$list_type_tag." class=\"pc-items\">\n" : ''; // only for ul/ol

                // Loop through each collected page ID
                foreach ($collected_page_ids as $page_id) {

                    // Get the page data by ID
                    $the_page = $motor->content->get_by_id($page_id);

                    // Ensure the page is of type 'page' and exists
                    // This prevents errors if the page ID is invalid or not a page
                    // TODO: Especially important while PageMotor has no multiple select field support
                    if ( ! $the_page || $the_page['type'] !== 'page') {
                        continue;
                    }

                    // Only output the page if it is live
                    if ($the_page['status'] !== 'live') {
                        continue;
                    }

                    $i++;

                    // Make sure the last item has no bottom margin if item spacing is enabled
                    $last_item = ($i === $count);
                    if ($last_item && $item_gap) {
                        $item_gap = ' style="margin-bottom:0;"';
                    }

                    // Finally, build and output the item
                    $title         = $the_page['title'];
                    $url           = $motor->url($the_page['slug']);
                    $content_words = explode(' ', $the_page['content']);
                    $is_truncated  = count($content_words) > $content_length;
                    $content       = implode(' ', array_slice($content_words, 0, $content_length));

                    // Wrapper for each item
                    echo "$tab\t\t<".$item_tag." class=\"pc-item pc-item-".$i."\" ".$item_gap.">\n";

                    // Title Tag
                    echo "$tab\t\t\t<".$title_tag." class=\"pc-item-title\" ".$align_left.">\n";
                    echo $title_link ? "$tab\t\t\t\t<a href=\"$url\">" : '';
                    echo "$title";
                    echo $title_link ? "</a>\n" : '';
                    echo "$tab\t\t\t</".$title_tag.">\n";

                    // Truncated Content
                    if ($show_content) {
                        echo "$tab\t\t\t<p class=\"pc-item-content\">\n";
                        echo "$tab\t\t\t\t".$motor->text($content, 'no-html');
                        if ($is_truncated) {
                            echo " [...]";
                        }
                        echo "\n$tab\t\t\t</p>\n";
                    }

                    // Read More Link
                    if ($show_content && $read_more_text) {
                        echo "$tab\t\t\t<div class=\"pc-item-read-more\">\n";
                        echo "$tab\t\t\t\t<a href=\"$url\" ".$link_style.">".$motor->text($read_more_text,
                                'no-html')."</a>\n";
                        echo "$tab\t\t\t</div>\n";
                    }

                    echo "$tab\t\t</".$item_tag.">\n"; // End of item wrapper
                }

                echo $list_type_tag ? "$tab\t</".$list_type_tag.">\n" : ''; // only for ul/ol

                echo "$tab</div>\n"; // End of collection wrapper
            }
        }
    }
}