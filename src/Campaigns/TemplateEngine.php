<?php
/**
 * Template Engine for Campaign Builder
 * 
 * Manages pre-built campaign templates including storage, categorization,
 * and integration with the Campaign Builder interface.
 *
 * @package WooOffers\Campaigns
 * @since 3.0.0
 */

namespace WooOffers\Campaigns;

use WooOffers\Core\SecurityManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Template Engine Class
 */
class TemplateEngine {

    /**
     * Custom Post Type name for templates
     */
    const POST_TYPE = 'campaign_template';
    
    /**
     * Custom Taxonomy name for template categories
     */
    const TAXONOMY = 'template_category';
    
    /**
     * Template meta key for configuration data
     */
    const META_CONFIG = '_template_config';
    
    /**
     * Template meta key for preview image
     */
    const META_PREVIEW = '_template_preview';
    
    /**
     * Template meta key for version
     */
    const META_VERSION = '_template_version';

    /**
     * Initialize the Template Engine
     */
    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_post_type' ] );
        add_action( 'init', [ __CLASS__, 'register_taxonomy' ] );
        add_action( 'init', [ __CLASS__, 'create_default_categories' ] );
        add_action( 'wp_loaded', [ __CLASS__, 'create_default_templates' ] );
        
        // AJAX endpoints for template operations
        add_action( 'wp_ajax_woo_offers_get_templates', [ __CLASS__, 'ajax_get_templates' ] );
        add_action( 'wp_ajax_woo_offers_save_template', [ __CLASS__, 'ajax_save_template' ] );
        add_action( 'wp_ajax_woo_offers_save_campaign_as_template', [ __CLASS__, 'ajax_save_campaign_as_template' ] );
        add_action( 'wp_ajax_woo_offers_import_template', [ __CLASS__, 'ajax_import_template' ] );
        add_action( 'wp_ajax_woo_offers_export_template', [ __CLASS__, 'ajax_export_template' ] );
        add_action( 'wp_ajax_woo_offers_delete_template', [ __CLASS__, 'ajax_delete_template' ] );
        
        // Admin hooks
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_template_meta_boxes' ] );
        add_action( 'save_post', [ __CLASS__, 'save_template_meta' ], 10, 2 );
    }

    /**
     * Register the Campaign Template Custom Post Type
     */
    public static function register_post_type() {
        $labels = [
            'name'                  => _x( 'Campaign Templates', 'Post type general name', 'woo-offers' ),
            'singular_name'         => _x( 'Campaign Template', 'Post type singular name', 'woo-offers' ),
            'menu_name'             => _x( 'Templates', 'Admin Menu text', 'woo-offers' ),
            'name_admin_bar'        => _x( 'Campaign Template', 'Add New on Toolbar', 'woo-offers' ),
            'add_new'               => __( 'Add New', 'woo-offers' ),
            'add_new_item'          => __( 'Add New Template', 'woo-offers' ),
            'new_item'              => __( 'New Template', 'woo-offers' ),
            'edit_item'             => __( 'Edit Template', 'woo-offers' ),
            'view_item'             => __( 'View Template', 'woo-offers' ),
            'all_items'             => __( 'All Templates', 'woo-offers' ),
            'search_items'          => __( 'Search Templates', 'woo-offers' ),
            'parent_item_colon'     => __( 'Parent Templates:', 'woo-offers' ),
            'not_found'             => __( 'No templates found.', 'woo-offers' ),
            'not_found_in_trash'    => __( 'No templates found in Trash.', 'woo-offers' ),
            'featured_image'        => _x( 'Template Preview Image', 'Overrides the "Featured Image" phrase', 'woo-offers' ),
            'set_featured_image'    => _x( 'Set preview image', 'Overrides the "Set featured image" phrase', 'woo-offers' ),
            'remove_featured_image' => _x( 'Remove preview image', 'Overrides the "Remove featured image" phrase', 'woo-offers' ),
            'use_featured_image'    => _x( 'Use as preview image', 'Overrides the "Use as featured image" phrase', 'woo-offers' ),
            'archives'              => _x( 'Template archives', 'The post type archive label', 'woo-offers' ),
            'insert_into_item'      => _x( 'Insert into template', 'Overrides the "Insert into post" phrase', 'woo-offers' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this template', 'Overrides the "Uploaded to this post" phrase', 'woo-offers' ),
            'filter_items_list'     => _x( 'Filter templates list', 'Screen reader text for the filter links', 'woo-offers' ),
            'items_list_navigation' => _x( 'Templates list navigation', 'Screen reader text for the pagination', 'woo-offers' ),
            'items_list'            => _x( 'Templates list', 'Screen reader text for the items list', 'woo-offers' ),
        ];

        $args = [
            'labels'                => $labels,
            'public'                => false,
            'publicly_queryable'    => false,
            'show_ui'               => true,
            'show_in_menu'          => 'woo-offers',
            'query_var'             => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'menu_position'         => 25,
            'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'show_in_rest'          => false,
            'menu_icon'             => 'dashicons-layout',
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Register the Template Category Custom Taxonomy
     */
    public static function register_taxonomy() {
        $labels = [
            'name'                       => _x( 'Template Categories', 'Taxonomy general name', 'woo-offers' ),
            'singular_name'              => _x( 'Template Category', 'Taxonomy singular name', 'woo-offers' ),
            'search_items'               => __( 'Search Categories', 'woo-offers' ),
            'popular_items'              => __( 'Popular Categories', 'woo-offers' ),
            'all_items'                  => __( 'All Categories', 'woo-offers' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Category', 'woo-offers' ),
            'update_item'                => __( 'Update Category', 'woo-offers' ),
            'add_new_item'               => __( 'Add New Category', 'woo-offers' ),
            'new_item_name'              => __( 'New Category Name', 'woo-offers' ),
            'separate_items_with_commas' => __( 'Separate categories with commas', 'woo-offers' ),
            'add_or_remove_items'        => __( 'Add or remove categories', 'woo-offers' ),
            'choose_from_most_used'      => __( 'Choose from the most used categories', 'woo-offers' ),
            'not_found'                  => __( 'No categories found.', 'woo-offers' ),
            'menu_name'                  => __( 'Categories', 'woo-offers' ),
        ];

        $args = [
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => false,
            'rewrite'               => false,
            'public'                => false,
            'show_in_rest'          => false,
        ];

        register_taxonomy( self::TAXONOMY, [ self::POST_TYPE ], $args );
    }

    /**
     * Create default template categories
     */
    public static function create_default_categories() {
        // Only create categories once
        if ( get_option( 'woo_offers_default_categories_created' ) ) {
            return;
        }

        $default_categories = [
            [
                'name' => 'BOGO',
                'description' => 'Buy One Get One offers and promotions',
                'slug' => 'bogo'
            ],
            [
                'name' => 'Discounts',
                'description' => 'Percentage and fixed amount discount campaigns',
                'slug' => 'discounts'
            ],
            [
                'name' => 'Product Promotions',
                'description' => 'Product-specific promotional campaigns',
                'slug' => 'product-promotions'
            ],
            [
                'name' => 'Exit Intent',
                'description' => 'Exit-intent popup campaigns',
                'slug' => 'exit-intent'
            ],
            [
                'name' => 'Upsells',
                'description' => 'Upsell and cross-sell campaigns',
                'slug' => 'upsells'
            ],
            [
                'name' => 'Free Shipping',
                'description' => 'Free shipping promotional campaigns',
                'slug' => 'free-shipping'
            ],
            [
                'name' => 'Seasonal',
                'description' => 'Holiday and seasonal promotional campaigns',
                'slug' => 'seasonal'
            ],
            [
                'name' => 'Cart Abandonment',
                'description' => 'Cart abandonment recovery campaigns',
                'slug' => 'cart-abandonment'
            ]
        ];

        foreach ( $default_categories as $category ) {
            if ( ! term_exists( $category['slug'], self::TAXONOMY ) ) {
                wp_insert_term( $category['name'], self::TAXONOMY, [
                    'description' => $category['description'],
                    'slug' => $category['slug']
                ] );
            }
        }

        update_option( 'woo_offers_default_categories_created', true );
    }

    /**
     * Create default templates
     */
    public static function create_default_templates() {
        // Check if templates already exist
        $existing_templates = get_posts( [
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'numberposts' => 1,
            'meta_query' => [
                [
                    'key' => '_default_template',
                    'value' => 'yes'
                ]
            ]
        ] );

        if ( ! empty( $existing_templates ) ) {
            return; // Default templates already created
        }

        // Define professional template configurations
        $templates = [
            // BOGO Templates
            [
                'title' => 'Classic BOGO Offer',
                'description' => 'A professional Buy One Get One Free template with product showcase and clear call-to-action.',
                'excerpt' => 'Perfect for increasing sales volume and moving inventory',
                'categories' => ['bogo'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'LIMITED TIME OFFER',
                                'fontSize' => '14px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#3b82f6',
                                'marginTop' => 20,
                                'marginBottom' => 10
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Buy One Get One FREE!',
                                'fontSize' => '32px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Double your value with our exclusive BOGO deal. Choose from our best-selling products and get the second one absolutely free!',
                                'fontSize' => '16px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#6b7280',
                                'marginTop' => 0,
                                'marginBottom' => 30
                            ]
                        ],
                        [
                            'type' => 'bogo-offer',
                            'properties' => [
                                'title' => 'Featured Products',
                                'description' => 'Select any product and get the second one free',
                                'backgroundColor' => '#3b82f6',
                                'textColor' => '#ffffff',
                                'borderRadius' => 12,
                                'showButton' => true,
                                'buttonText' => 'Shop BOGO Deal',
                                'marginTop' => 20,
                                'marginBottom' => 30
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '⏰ Offer expires in 3 days • 🚚 Free shipping on all orders • 💫 30-day money-back guarantee',
                                'fontSize' => '13px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#9ca3af',
                                'marginTop' => 20,
                                'marginBottom' => 20
                            ]
                        ]
                    ],
                    'settings' => [
                        'backgroundColor' => '#ffffff',
                        'padding' => 40,
                        'maxWidth' => 600
                    ]
                ]
            ],

            [
                'title' => 'Product Bundle BOGO',
                'description' => 'A sophisticated BOGO template designed for product bundles and collections.',
                'excerpt' => 'Ideal for promoting product bundles and increasing average order value',
                'categories' => ['bogo', 'product-promotions'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'BUNDLE & SAVE',
                                'fontSize' => '16px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#10b981',
                                'marginTop' => 25,
                                'marginBottom' => 10
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Buy Any Bundle, Get Another FREE',
                                'fontSize' => '28px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'columns',
                            'properties' => [
                                'columnCount' => '2',
                                'gap' => 20,
                                'alignment' => 'center',
                                'marginTop' => 25,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'bogo-offer',
                            'properties' => [
                                'title' => 'Premium Bundle Collection',
                                'description' => 'Choose from our curated product bundles',
                                'backgroundColor' => '#10b981',
                                'textColor' => '#ffffff',
                                'borderRadius' => 8,
                                'showButton' => true,
                                'buttonText' => 'Browse Bundles',
                                'marginTop' => 15,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'divider',
                            'properties' => [
                                'style' => 'solid',
                                'thickness' => 1,
                                'color' => '#e5e7eb',
                                'width' => '75%',
                                'alignment' => 'center',
                                'marginTop' => 25,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Valid for all bundle products • Automatic discount applied at checkout',
                                'fontSize' => '14px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#6b7280',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ]
                    ]
                ]
            ],

            // Discount Templates
            [
                'title' => 'Flash Sale 30% Off',
                'description' => 'High-impact flash sale template with urgency indicators and clear value proposition.',
                'excerpt' => 'Perfect for time-sensitive promotions and creating urgency',
                'categories' => ['discounts', 'seasonal'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '🔥 FLASH SALE ALERT',
                                'fontSize' => '18px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#dc2626',
                                'marginTop' => 20,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '30% OFF',
                                'fontSize' => '48px',
                                'fontWeight' => '900',
                                'textAlign' => 'center',
                                'color' => '#dc2626',
                                'marginTop' => 0,
                                'marginBottom' => 10
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Everything Must Go!',
                                'fontSize' => '24px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'discount-offer',
                            'properties' => [
                                'discountType' => 'percentage',
                                'discountValue' => 30,
                                'title' => 'SAVE BIG TODAY',
                                'description' => 'Use code FLASH30 at checkout',
                                'backgroundColor' => '#dc2626',
                                'textColor' => '#ffffff',
                                'borderRadius' => 10,
                                'showButton' => true,
                                'buttonText' => 'Shop Sale Now',
                                'marginTop' => 25,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '⏰ Sale ends in 24 hours • 📦 Free shipping over $50 • 🏃‍♂️ Limited quantities available',
                                'fontSize' => '14px',
                                'fontWeight' => '500',
                                'textAlign' => 'center',
                                'color' => '#7c2d12',
                                'marginTop' => 20,
                                'marginBottom' => 20
                            ]
                        ]
                    ],
                    'settings' => [
                        'backgroundColor' => '#fef2f2',
                        'padding' => 35
                    ]
                ]
            ],

            [
                'title' => 'Seasonal 20% Discount',
                'description' => 'Elegant seasonal discount template with clean design and professional appearance.',
                'excerpt' => 'Great for seasonal sales and holiday promotions',
                'categories' => ['discounts', 'seasonal'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'SEASONAL SAVINGS',
                                'fontSize' => '14px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#f59e0b',
                                'marginTop' => 25,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Save 20% on Selected Items',
                                'fontSize' => '30px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Refresh your collection with our handpicked seasonal favorites. Quality products at unbeatable prices.',
                                'fontSize' => '16px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#6b7280',
                                'marginTop' => 0,
                                'marginBottom' => 30
                            ]
                        ],
                        [
                            'type' => 'discount-offer',
                            'properties' => [
                                'discountType' => 'percentage',
                                'discountValue' => 20,
                                'title' => 'Seasonal Collection',
                                'description' => 'Premium quality at seasonal prices',
                                'backgroundColor' => '#f59e0b',
                                'textColor' => '#ffffff',
                                'borderRadius' => 8,
                                'showButton' => true,
                                'buttonText' => 'Explore Collection',
                                'marginTop' => 20,
                                'marginBottom' => 30
                            ]
                        ],
                        [
                            'type' => 'columns',
                            'properties' => [
                                'columnCount' => '3',
                                'gap' => 15,
                                'alignment' => 'center',
                                'marginTop' => 25,
                                'marginBottom' => 25
                            ]
                        ]
                    ]
                ]
            ],

            // Product Promotion Templates
            [
                'title' => 'New Product Launch',
                'description' => 'Professional product launch template with feature highlights and early bird pricing.',
                'excerpt' => 'Perfect for introducing new products with special launch offers',
                'categories' => ['product-promotions'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '🚀 NEW ARRIVAL',
                                'fontSize' => '16px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#8b5cf6',
                                'marginTop' => 25,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Introducing Our Latest Innovation',
                                'fontSize' => '28px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'image',
                            'properties' => [
                                'src' => '',
                                'alt' => 'New Product Image',
                                'width' => '75%',
                                'alignment' => 'center',
                                'borderRadius' => 12,
                                'marginTop' => 20,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Experience the next level of quality and performance. Built with premium materials and cutting-edge technology.',
                                'fontSize' => '16px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#4b5563',
                                'marginTop' => 0,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'button',
                            'properties' => [
                                'text' => 'Pre-Order Now - 15% Off',
                                'url' => '#',
                                'style' => 'primary',
                                'size' => 'lg',
                                'alignment' => 'center',
                                'backgroundColor' => '#8b5cf6',
                                'textColor' => '#ffffff',
                                'borderRadius' => 8,
                                'marginTop' => 20,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '✅ Free shipping • 🔒 Secure checkout • 📞 24/7 support',
                                'fontSize' => '13px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#9ca3af',
                                'marginTop' => 15,
                                'marginBottom' => 20
                            ]
                        ]
                    ]
                ]
            ],

            // Free Shipping Template
            [
                'title' => 'Free Shipping Offer',
                'description' => 'Clean and attractive free shipping promotion with minimum order threshold.',
                'excerpt' => 'Encourage larger orders with attractive shipping incentives',
                'categories' => ['free-shipping'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '🚚 FREE SHIPPING',
                                'fontSize' => '20px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#059669',
                                'marginTop' => 25,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'On Orders Over $75',
                                'fontSize' => '32px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Shop your favorites and enjoy free delivery right to your door. No minimum order, no hidden fees.',
                                'fontSize' => '16px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#6b7280',
                                'marginTop' => 0,
                                'marginBottom' => 30
                            ]
                        ],
                        [
                            'type' => 'button',
                            'properties' => [
                                'text' => 'Shop Free Shipping',
                                'url' => '#',
                                'style' => 'primary',
                                'size' => 'lg',
                                'alignment' => 'center',
                                'backgroundColor' => '#059669',
                                'textColor' => '#ffffff',
                                'borderRadius' => 25,
                                'marginTop' => 25,
                                'marginBottom' => 30
                            ]
                        ],
                        [
                            'type' => 'divider',
                            'properties' => [
                                'style' => 'solid',
                                'thickness' => 1,
                                'color' => '#d1fae5',
                                'width' => '50%',
                                'alignment' => 'center',
                                'marginTop' => 20,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Standard shipping rates apply to orders under $75 • Expedited shipping options available',
                                'fontSize' => '12px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#9ca3af',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ]
                    ],
                    'settings' => [
                        'backgroundColor' => '#f0fdf4',
                        'padding' => 40
                    ]
                ]
            ],

            // Exit Intent Template
            [
                'title' => 'Exit Intent Discount',
                'description' => 'Compelling exit intent template designed to recover abandoning visitors with an exclusive offer.',
                'excerpt' => 'Recover potential lost sales with targeted exit offers',
                'categories' => ['exit-intent'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Wait! Don\'t Leave Empty Handed',
                                'fontSize' => '26px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#dc2626',
                                'marginTop' => 20,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Get 15% OFF Your First Order',
                                'fontSize' => '22px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 0,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Join thousands of satisfied customers and get exclusive access to deals, new arrivals, and insider tips.',
                                'fontSize' => '16px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#6b7280',
                                'marginTop' => 0,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'discount-offer',
                            'properties' => [
                                'discountType' => 'percentage',
                                'discountValue' => 15,
                                'title' => 'Exclusive Welcome Offer',
                                'description' => 'Use code WELCOME15 at checkout',
                                'backgroundColor' => '#dc2626',
                                'textColor' => '#ffffff',
                                'borderRadius' => 8,
                                'showButton' => true,
                                'buttonText' => 'Claim My Discount',
                                'marginTop' => 20,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => '⏰ This offer expires in 10 minutes • 📧 Sent directly to your inbox',
                                'fontSize' => '13px',
                                'fontWeight' => '500',
                                'textAlign' => 'center',
                                'color' => '#7c2d12',
                                'marginTop' => 15,
                                'marginBottom' => 20
                            ]
                        ]
                    ]
                ]
            ],

            // Upsell Template
            [
                'title' => 'Product Upsell Bundle',
                'description' => 'Strategic upsell template showcasing complementary products and bundle savings.',
                'excerpt' => 'Increase average order value with smart product recommendations',
                'categories' => ['upsells'],
                'config' => [
                    'components' => [
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Complete Your Purchase',
                                'fontSize' => '24px',
                                'fontWeight' => '700',
                                'textAlign' => 'center',
                                'color' => '#1f2937',
                                'marginTop' => 25,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Customers who bought this item also purchased:',
                                'fontSize' => '16px',
                                'fontWeight' => 'normal',
                                'textAlign' => 'center',
                                'color' => '#6b7280',
                                'marginTop' => 0,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'columns',
                            'properties' => [
                                'columnCount' => '2',
                                'gap' => 20,
                                'alignment' => 'center',
                                'marginTop' => 20,
                                'marginBottom' => 25
                            ]
                        ],
                        [
                            'type' => 'text',
                            'properties' => [
                                'content' => 'Bundle & Save 25%',
                                'fontSize' => '20px',
                                'fontWeight' => '600',
                                'textAlign' => 'center',
                                'color' => '#059669',
                                'marginTop' => 25,
                                'marginBottom' => 15
                            ]
                        ],
                        [
                            'type' => 'button',
                            'properties' => [
                                'text' => 'Add Bundle to Cart',
                                'url' => '#',
                                'style' => 'primary',
                                'size' => 'lg',
                                'alignment' => 'center',
                                'backgroundColor' => '#3b82f6',
                                'textColor' => '#ffffff',
                                'borderRadius' => 6,
                                'marginTop' => 20,
                                'marginBottom' => 20
                            ]
                        ],
                        [
                            'type' => 'button',
                            'properties' => [
                                'text' => 'No Thanks, Continue',
                                'url' => '#',
                                'style' => 'text',
                                'size' => 'sm',
                                'alignment' => 'center',
                                'backgroundColor' => 'transparent',
                                'textColor' => '#6b7280',
                                'borderRadius' => 4,
                                'marginTop' => 0,
                                'marginBottom' => 25
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Create templates
        foreach ( $templates as $template_data ) {
            $post_data = [
                'post_title' => $template_data['title'],
                'post_content' => $template_data['description'],
                'post_excerpt' => $template_data['excerpt'],
                'post_status' => 'publish',
                'post_type' => self::POST_TYPE,
                'post_author' => 1 // Admin user
            ];

            $template_id = wp_insert_post( $post_data );

            if ( ! is_wp_error( $template_id ) ) {
                // Store template configuration
                update_post_meta( $template_id, self::META_CONFIG, wp_json_encode( $template_data['config'] ) );
                update_post_meta( $template_id, self::META_VERSION, '1.0.0' );
                update_post_meta( $template_id, '_default_template', 'yes' );

                // Generate preview placeholder
                self::generate_template_preview( $template_id, '' );

                // Assign categories
                $category_ids = [];
                foreach ( $template_data['categories'] as $category_slug ) {
                    $term = get_term_by( 'slug', $category_slug, self::TAXONOMY );
                    if ( $term ) {
                        $category_ids[] = $term->term_id;
                    }
                }
                if ( ! empty( $category_ids ) ) {
                    wp_set_post_terms( $template_id, $category_ids, self::TAXONOMY );
                }
            }
        }
    }

    /**
     * Get template by ID
     * 
     * @param int $template_id Template post ID
     * @return array|false Template data or false if not found
     */
    public static function get_template( $template_id ) {
        $post = get_post( $template_id );
        
        if ( ! $post || $post->post_type !== self::POST_TYPE ) {
            return false;
        }

        $config = get_post_meta( $template_id, self::META_CONFIG, true );
        $version = get_post_meta( $template_id, self::META_VERSION, true );
        $preview = get_post_meta( $template_id, self::META_PREVIEW, true );
        
        // Get categories
        $categories = wp_get_post_terms( $template_id, self::TAXONOMY, [ 'fields' => 'all' ] );
        
        // Get featured image URL for preview
        $preview_image = '';
        if ( has_post_thumbnail( $template_id ) ) {
            $preview_image = get_the_post_thumbnail_url( $template_id, 'medium' );
        }

        return [
            'id' => $template_id,
            'title' => $post->post_title,
            'description' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'config' => $config ? json_decode( $config, true ) : [],
            'version' => $version ?: '1.0.0',
            'preview' => $preview ?: $preview_image,
            'categories' => $categories,
            'created' => $post->post_date,
            'modified' => $post->post_modified
        ];
    }

    /**
     * Get all templates with optional filtering
     * 
     * @param array $args Query arguments
     * @return array Array of template data
     */
    public static function get_templates( $args = [] ) {
        $defaults = [
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ];

        $args = wp_parse_args( $args, $defaults );

        $posts = get_posts( $args );
        $templates = [];

        foreach ( $posts as $post ) {
            $template = self::get_template( $post->ID );
            if ( $template ) {
                $templates[] = $template;
            }
        }

        return $templates;
    }

    /**
     * Get template categories
     * 
     * @return array Array of category terms
     */
    public static function get_categories() {
        return get_terms( [
            'taxonomy' => self::TAXONOMY,
            'hide_empty' => false,
            'orderby' => 'name'
        ] );
    }

    /**
     * Save campaign as template
     * 
     * @param array $campaign_data Campaign data from Campaign Builder
     * @param array $template_meta Template metadata (title, description, etc.)
     * @return int|WP_Error Template post ID or error
     */
    public static function save_campaign_as_template( $campaign_data, $template_meta ) {
        // Validate required data
        if ( empty( $campaign_data ) || empty( $template_meta['title'] ) ) {
            return new WP_Error( 'missing_data', __( 'Campaign data and template title are required.', 'woo-offers' ) );
        }

        // Create template post
        $post_data = [
            'post_title' => sanitize_text_field( $template_meta['title'] ),
            'post_content' => sanitize_textarea_field( $template_meta['description'] ?? '' ),
            'post_excerpt' => sanitize_textarea_field( $template_meta['excerpt'] ?? '' ),
            'post_status' => 'publish',
            'post_type' => self::POST_TYPE,
            'post_author' => get_current_user_id()
        ];

        $template_id = wp_insert_post( $post_data );

        if ( is_wp_error( $template_id ) ) {
            return $template_id;
        }

        // Store template configuration
        $config_json = wp_json_encode( $campaign_data );
        update_post_meta( $template_id, self::META_CONFIG, $config_json );
        
        // Store version info
        update_post_meta( $template_id, self::META_VERSION, $template_meta['version'] ?? '1.0.0' );
        
        // Store preview URL if provided
        if ( ! empty( $template_meta['preview_url'] ) ) {
            update_post_meta( $template_id, self::META_PREVIEW, esc_url_raw( $template_meta['preview_url'] ) );
        }

        // Assign categories if provided
        if ( ! empty( $template_meta['categories'] ) ) {
            $category_ids = [];
            foreach ( $template_meta['categories'] as $category_slug ) {
                $term = get_term_by( 'slug', $category_slug, self::TAXONOMY );
                if ( $term ) {
                    $category_ids[] = $term->term_id;
                }
            }
            if ( ! empty( $category_ids ) ) {
                wp_set_post_terms( $template_id, $category_ids, self::TAXONOMY );
            }
        }

        return $template_id;
    }

    /**
     * Generate preview image for template
     * 
     * @param int $template_id Template post ID
     * @param string $preview_html HTML content for preview
     * @return string|false Preview image URL or false on failure
     */
    public static function generate_template_preview( $template_id, $preview_html ) {
        // For now, return a placeholder. In a full implementation, this could:
        // 1. Use a headless browser service to capture screenshots
        // 2. Generate a simple preview image with template info
        // 3. Use WordPress's built-in image generation if available
        
        $placeholder_svg = 'data:image/svg+xml;base64,' . base64_encode( 
            '<svg width="300" height="200" viewBox="0 0 300 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="300" height="200" fill="#F3F4F6"/>
                <rect x="20" y="20" width="260" height="160" fill="#FFFFFF" stroke="#E5E7EB"/>
                <rect x="40" y="40" width="220" height="20" fill="#3B82F6"/>
                <rect x="40" y="80" width="180" height="12" fill="#6B7280"/>
                <rect x="40" y="100" width="160" height="12" fill="#6B7280"/>
                <rect x="40" y="140" width="100" height="30" fill="#10B981"/>
                <text x="150" y="195" font-family="Arial, sans-serif" font-size="10" fill="#6B7280" text-anchor="middle">Template Preview</text>
            </svg>' 
        );

        // Store the preview URL
        update_post_meta( $template_id, self::META_PREVIEW, $placeholder_svg );
        
        return $placeholder_svg;
    }

    /**
     * AJAX: Get templates
     */
    public static function ajax_get_templates() {
        // Security check
        if ( ! SecurityManager::verify_ajax_nonce() || ! current_user_can( 'edit_posts' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ), 403 );
        }

        $category_filter = sanitize_text_field( $_POST['category'] ?? '' );
        
        $args = [];
        if ( $category_filter && $category_filter !== 'all' ) {
            $args['tax_query'] = [
                [
                    'taxonomy' => self::TAXONOMY,
                    'field' => 'slug',
                    'terms' => $category_filter
                ]
            ];
        }

        $templates = self::get_templates( $args );
        $categories = self::get_categories();

        // Format templates for frontend
        $formatted_templates = array_map( function( $template ) {
            return [
                'id' => $template['id'],
                'title' => $template['title'],
                'description' => $template['description'],
                'excerpt' => $template['excerpt'],
                'preview' => $template['preview'],
                'categories' => wp_list_pluck( $template['categories'], 'slug' ),
                'category_names' => wp_list_pluck( $template['categories'], 'name' ),
                'config' => $template['config'],
                'version' => $template['version']
            ];
        }, $templates );

        // Format categories for frontend
        $formatted_categories = array_map( function( $category ) {
            return [
                'id' => $category->term_id,
                'slug' => $category->slug,
                'name' => $category->name,
                'description' => $category->description,
                'count' => $category->count
            ];
        }, $categories );

        wp_send_json_success( [
            'templates' => $formatted_templates,
            'categories' => $formatted_categories
        ] );
    }

    /**
     * AJAX: Save campaign as template
     */
    public static function ajax_save_campaign_as_template() {
        // Security check
        if ( ! SecurityManager::verify_ajax_nonce() || ! current_user_can( 'edit_posts' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ), 403 );
        }

        $campaign_data = json_decode( stripslashes( $_POST['campaign_data'] ?? '{}' ), true );
        $template_meta = [
            'title' => sanitize_text_field( $_POST['template_title'] ?? '' ),
            'description' => sanitize_textarea_field( $_POST['template_description'] ?? '' ),
            'excerpt' => sanitize_textarea_field( $_POST['template_excerpt'] ?? '' ),
            'categories' => array_map( 'sanitize_text_field', $_POST['template_categories'] ?? [] ),
            'version' => sanitize_text_field( $_POST['template_version'] ?? '1.0.0' )
        ];

        if ( empty( $campaign_data ) || empty( $template_meta['title'] ) ) {
            wp_send_json_error( [
                'message' => __( 'Campaign data and template title are required.', 'woo-offers' )
            ] );
        }

        $template_id = self::save_campaign_as_template( $campaign_data, $template_meta );

        if ( is_wp_error( $template_id ) ) {
            wp_send_json_error( [
                'message' => $template_id->get_error_message()
            ] );
        }

        // Generate preview image
        $preview_html = $_POST['preview_html'] ?? '';
        if ( ! empty( $preview_html ) ) {
            self::generate_template_preview( $template_id, $preview_html );
        }

        wp_send_json_success( [
            'template_id' => $template_id,
            'message' => __( 'Template saved successfully!', 'woo-offers' )
        ] );
    }

    /**
     * Export template as JSON file
     * 
     * @param int $template_id Template post ID
     * @return array Export data or error
     */
    public static function export_template( $template_id ) {
        $template = self::get_template( $template_id );
        
        if ( ! $template ) {
            return new WP_Error( 'template_not_found', __( 'Template not found.', 'woo-offers' ) );
        }

        // Prepare export data
        $export_data = [
            'template' => [
                'title' => $template['title'],
                'description' => $template['description'],
                'excerpt' => $template['excerpt'],
                'config' => $template['config'],
                'version' => $template['version'],
                'categories' => wp_list_pluck( $template['categories'], 'slug' ),
                'preview' => $template['preview']
            ],
            'meta' => [
                'export_version' => '1.0.0',
                'export_date' => current_time( 'mysql' ),
                'plugin_version' => '3.0.0',
                'wordpress_version' => get_bloginfo( 'version' ),
                'exported_by' => wp_get_current_user()->display_name
            ]
        ];

        return $export_data;
    }

    /**
     * Import template from data
     * 
     * @param array $import_data Template import data
     * @param array $options Import options (overwrite, category_mapping, etc.)
     * @return int|WP_Error Template post ID or error
     */
    public static function import_template( $import_data, $options = [] ) {
        // Validate import data structure
        if ( empty( $import_data['template'] ) || empty( $import_data['meta'] ) ) {
            return new WP_Error( 'invalid_import_data', __( 'Invalid template import data.', 'woo-offers' ) );
        }

        $template_data = $import_data['template'];
        $meta_data = $import_data['meta'];

        // Validate required fields
        if ( empty( $template_data['title'] ) || empty( $template_data['config'] ) ) {
            return new WP_Error( 'missing_required_fields', __( 'Template title and configuration are required.', 'woo-offers' ) );
        }

        // Check if template with same title exists
        $existing_template = get_posts( [
            'post_type' => self::POST_TYPE,
            'title' => $template_data['title'],
            'post_status' => 'publish',
            'numberposts' => 1
        ] );

        if ( ! empty( $existing_template ) && empty( $options['overwrite'] ) ) {
            return new WP_Error( 'template_exists', sprintf( 
                __( 'Template "%s" already exists. Enable overwrite to replace it.', 'woo-offers' ), 
                $template_data['title'] 
            ) );
        }

        // Create or update template post
        $post_data = [
            'post_title' => sanitize_text_field( $template_data['title'] ),
            'post_content' => sanitize_textarea_field( $template_data['description'] ?? '' ),
            'post_excerpt' => sanitize_textarea_field( $template_data['excerpt'] ?? '' ),
            'post_status' => 'publish',
            'post_type' => self::POST_TYPE,
            'post_author' => get_current_user_id()
        ];

        // If overwriting, set the post ID
        if ( ! empty( $existing_template ) && ! empty( $options['overwrite'] ) ) {
            $post_data['ID'] = $existing_template[0]->ID;
        }

        $template_id = wp_insert_post( $post_data );

        if ( is_wp_error( $template_id ) ) {
            return $template_id;
        }

        // Store template configuration
        $config_json = is_string( $template_data['config'] ) ? 
            $template_data['config'] : 
            wp_json_encode( $template_data['config'] );
        update_post_meta( $template_id, self::META_CONFIG, $config_json );
        
        // Store version info
        update_post_meta( $template_id, self::META_VERSION, $template_data['version'] ?? '1.0.0' );
        
        // Store preview if available
        if ( ! empty( $template_data['preview'] ) ) {
            update_post_meta( $template_id, self::META_PREVIEW, esc_url_raw( $template_data['preview'] ) );
        }

        // Assign categories
        if ( ! empty( $template_data['categories'] ) ) {
            $category_ids = [];
            foreach ( $template_data['categories'] as $category_slug ) {
                // Map category if mapping provided
                $mapped_slug = $options['category_mapping'][ $category_slug ] ?? $category_slug;
                
                $term = get_term_by( 'slug', $mapped_slug, self::TAXONOMY );
                if ( ! $term ) {
                    // Create category if it doesn't exist
                    $new_term = wp_insert_term( ucfirst( str_replace( '-', ' ', $mapped_slug ) ), self::TAXONOMY, [
                        'slug' => $mapped_slug
                    ] );
                    if ( ! is_wp_error( $new_term ) ) {
                        $category_ids[] = $new_term['term_id'];
                    }
                } else {
                    $category_ids[] = $term->term_id;
                }
            }
            if ( ! empty( $category_ids ) ) {
                wp_set_post_terms( $template_id, $category_ids, self::TAXONOMY );
            }
        }

        // Store import metadata
        update_post_meta( $template_id, '_imported_from', [
            'import_date' => current_time( 'mysql' ),
            'original_export_date' => $meta_data['export_date'] ?? '',
            'original_author' => $meta_data['exported_by'] ?? '',
            'plugin_version' => $meta_data['plugin_version'] ?? '',
            'import_method' => 'manual'
        ] );

        return $template_id;
    }

    /**
     * Generate export filename
     * 
     * @param array $template Template data
     * @return string Filename
     */
    public static function generate_export_filename( $template ) {
        $title = sanitize_title( $template['title'] );
        $date = date( 'Y-m-d' );
        return "woo-offers-template-{$title}-{$date}.json";
    }

    /**
     * Validate import file
     * 
     * @param string $file_path Path to import file
     * @return array|WP_Error Validation result
     */
    public static function validate_import_file( $file_path ) {
        // Check file exists and is readable
        if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
            return new WP_Error( 'file_not_readable', __( 'Import file is not readable.', 'woo-offers' ) );
        }

        // Check file size (max 5MB)
        $file_size = filesize( $file_path );
        if ( $file_size > 5 * 1024 * 1024 ) {
            return new WP_Error( 'file_too_large', __( 'Import file is too large. Maximum size is 5MB.', 'woo-offers' ) );
        }

        // Read and validate JSON
        $file_contents = file_get_contents( $file_path );
        if ( $file_contents === false ) {
            return new WP_Error( 'file_read_error', __( 'Unable to read import file.', 'woo-offers' ) );
        }

        $import_data = json_decode( $file_contents, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'invalid_json', __( 'Import file contains invalid JSON.', 'woo-offers' ) );
        }

        // Validate structure
        if ( empty( $import_data['template'] ) || empty( $import_data['meta'] ) ) {
            return new WP_Error( 'invalid_structure', __( 'Import file does not contain valid template data.', 'woo-offers' ) );
        }

        // Check plugin compatibility
        if ( ! empty( $import_data['meta']['plugin_version'] ) ) {
            $import_version = $import_data['meta']['plugin_version'];
            $current_version = '3.0.0'; // Plugin version
            
            if ( version_compare( $import_version, '2.0.0', '<' ) ) {
                return new WP_Error( 'version_incompatible', sprintf(
                    __( 'Template was exported from an incompatible plugin version (%s). Minimum version required: 2.0.0', 'woo-offers' ),
                    $import_version
                ) );
            }
        }

        return [
            'data' => $import_data,
            'file_size' => $file_size,
            'template_count' => 1,
            'categories' => $import_data['template']['categories'] ?? []
        ];
    }

    /**
     * AJAX: Export template
     */
    public static function ajax_export_template() {
        // Security check
        if ( ! SecurityManager::verify_ajax_nonce() || ! current_user_can( 'edit_posts' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ), 403 );
        }

        $template_id = absint( $_POST['template_id'] ?? 0 );
        if ( ! $template_id ) {
            wp_send_json_error( [
                'message' => __( 'Template ID is required.', 'woo-offers' )
            ] );
        }

        $export_data = self::export_template( $template_id );
        
        if ( is_wp_error( $export_data ) ) {
            wp_send_json_error( [
                'message' => $export_data->get_error_message()
            ] );
        }

        $template = self::get_template( $template_id );
        $filename = self::generate_export_filename( $template );

        wp_send_json_success( [
            'data' => $export_data,
            'filename' => $filename,
            'message' => __( 'Template exported successfully!', 'woo-offers' )
        ] );
    }

    /**
     * AJAX: Import template
     */
    public static function ajax_import_template() {
        // Security check
        if ( ! SecurityManager::verify_ajax_nonce() || ! current_user_can( 'edit_posts' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ), 403 );
        }

        // Handle both file upload and JSON data
        $import_data = null;
        $filename = '';

        if ( ! empty( $_FILES['template_file'] ) && $_FILES['template_file']['error'] === UPLOAD_ERR_OK ) {
            // File upload import
            $uploaded_file = $_FILES['template_file'];
            $filename = sanitize_file_name( $uploaded_file['name'] );
            
            // Validate file type
            $file_extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
            if ( $file_extension !== 'json' ) {
                wp_send_json_error( [
                    'message' => __( 'Please upload a valid JSON file.', 'woo-offers' )
                ] );
            }

            // Validate file
            $validation_result = self::validate_import_file( $uploaded_file['tmp_name'] );
            if ( is_wp_error( $validation_result ) ) {
                wp_send_json_error( [
                    'message' => $validation_result->get_error_message()
                ] );
            }

            $import_data = $validation_result['data'];
            
        } elseif ( ! empty( $_POST['template_data'] ) ) {
            // JSON data import
            $json_data = stripslashes( $_POST['template_data'] );
            $import_data = json_decode( $json_data, true );
            
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                wp_send_json_error( [
                    'message' => __( 'Invalid JSON data provided.', 'woo-offers' )
                ] );
            }
            
        } else {
            wp_send_json_error( [
                'message' => __( 'No template data or file provided.', 'woo-offers' )
            ] );
        }

        // Parse import options
        $import_options = [
            'overwrite' => ! empty( $_POST['overwrite_existing'] ),
            'category_mapping' => []
        ];

        // Handle category mapping if provided
        if ( ! empty( $_POST['category_mapping'] ) ) {
            $mapping_data = json_decode( stripslashes( $_POST['category_mapping'] ), true );
            if ( is_array( $mapping_data ) ) {
                $import_options['category_mapping'] = $mapping_data;
            }
        }

        // Import the template
        $template_id = self::import_template( $import_data, $import_options );

        if ( is_wp_error( $template_id ) ) {
            wp_send_json_error( [
                'message' => $template_id->get_error_message()
            ] );
        }

        $template = self::get_template( $template_id );
        
        wp_send_json_success( [
            'template_id' => $template_id,
            'template' => [
                'id' => $template['id'],
                'title' => $template['title'],
                'description' => $template['description'],
                'categories' => wp_list_pluck( $template['categories'], 'name' )
            ],
            'filename' => $filename,
            'message' => sprintf( 
                __( 'Template "%s" imported successfully!', 'woo-offers' ), 
                $template['title'] 
            )
        ] );
    }

    /**
     * AJAX: Delete template (placeholder)
     */
    public static function ajax_delete_template() {
        // Security check
        if ( ! SecurityManager::verify_ajax_nonce() || ! current_user_can( 'delete_posts' ) ) {
            wp_die( __( 'Security check failed.', 'woo-offers' ), 403 );
        }

        wp_send_json_success( [
            'message' => 'Delete template functionality ready!'
        ] );
    }

    /**
     * Add template meta boxes (placeholder)
     */
    public static function add_template_meta_boxes() {
        // This will be implemented in the next step
    }

    /**
     * Save template meta data (placeholder)
     */
    public static function save_template_meta( $post_id, $post ) {
        // This will be implemented in the next step
    }
} 