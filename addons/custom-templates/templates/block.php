<?php

class WPURP_Template_Block {

    public $type;
    public $children = array();
    public $settings = array();
    public $style = array();
    public $conditions = array();

    // Relative block position
    public $parent;
    public $row;
    public $column;
    public $order;

    // Responsive condition
    protected $show_on_desktop = true;
    protected $show_on_mobile = true;

    // Special cases
    protected $link_color = false;
    protected $background_preset = false;

    public function __construct( $type )
    {
        $this->type = $type;
    }

    /*
     * Children
     */

    public function add_child( $block )
    {
        $this->children[$block->row][$block->column][] = $block;
    }

    public function output_children( $recipe, $row = 0, $column = 0 )
    {
        if( isset( $this->children[$row][$column] ) ) {
            foreach( $this->children[$row][$column] as $child )
            {
                echo $child->output( $recipe );
            }
        }
    }

    /*
     * Settings
     */

    public function add_settings( $block )
    {
        $this->settings = $block;

        /*
         * Styling
         */

        // Positioning
        if( $this->present( $block, 'float' )  && $block->float != 'none' ) $this->add_style( 'float', $block->float );
        if( $this->present( $block, 'center' ) && $block->center ) $this->add_style( 'margin', '0 auto' );
        if( $this->present( $block, 'marginTop' ) )     $this->add_style( 'margin-top',       $block->marginTop . 'px' );
        if( $this->present( $block, 'marginBottom' ) )  $this->add_style( 'margin-bottom',    $block->marginBottom . 'px' );
        if( $this->present( $block, 'marginLeft' ) )    $this->add_style( 'margin-left',      $block->marginLeft . 'px' );
        if( $this->present( $block, 'marginRight' ) )   $this->add_style( 'margin-right',     $block->marginRight . 'px' );

        if( $this->present( $block, 'paddingTop' ) )    $this->add_style( 'padding-top',      $block->paddingTop . 'px' );
        if( $this->present( $block, 'paddingBottom' ) ) $this->add_style( 'padding-bottom',   $block->paddingBottom . 'px' );
        if( $this->present( $block, 'paddingLeft' ) )   $this->add_style( 'padding-left',     $block->paddingLeft . 'px' );
        if( $this->present( $block, 'paddingRight' ) )  $this->add_style( 'padding-right',    $block->paddingRight . 'px' );
        if( $this->present( $block, 'paddingTop' ) )    $this->add_style( 'padding-top',      $block->paddingTop . 'px', 'td' );
        if( $this->present( $block, 'paddingBottom' ) ) $this->add_style( 'padding-bottom',   $block->paddingBottom . 'px', 'td' );
        if( $this->present( $block, 'paddingLeft' ) )   $this->add_style( 'padding-left',     $block->paddingLeft . 'px', 'td' );
        if( $this->present( $block, 'paddingRight' ) )  $this->add_style( 'padding-right',    $block->paddingRight . 'px', 'td' );

        if( $this->present( $block, 'width' ) )     $this->add_style( 'width', $block->width . $block->widthType );
        if( $this->present( $block, 'height' ) )    $this->add_style( 'height', $block->height . $block->heightType );
        if( $this->present( $block, 'maxWidth' ) )  $this->add_style( 'max-width', $block->maxWidth . $block->maxWidthType );
        if( $this->present( $block, 'maxHeight' ) ) $this->add_style( 'max-height', $block->maxHeight . $block->maxHeightType );
        if( $this->present( $block, 'minWidth' ) )  $this->add_style( 'min-width', $block->minWidth . $block->minWidthType );
        if( $this->present( $block, 'minHeight' ) ) $this->add_style( 'min-height', $block->minHeight . $block->minHeightType );

        if( $this->present( $block, 'position' ) ) {
            $this->add_style( 'position',      $block->position );

            if( $block->position != 'static' ) {
                if( $this->present( $block, 'positionTop' ) )    $this->add_style( 'top',      $block->positionTop . 'px' );
                if( $this->present( $block, 'positionBottom' ) ) $this->add_style( 'bottom',   $block->positionBottom . 'px' );
                if( $this->present( $block, 'positionLeft' ) )   $this->add_style( 'left',     $block->positionLeft . 'px' );
                if( $this->present( $block, 'positionRight' ) )  $this->add_style( 'right',    $block->positionRight . 'px' );
            }
        }

        // Block Style
        if( $this->present( $block, 'backgroundPreset' ) ) { $this->background_preset = $block->backgroundPreset; }
        if( $this->present( $block, 'backgroundImage' ) ) $this->add_style( 'background', 'url(' . $block->backgroundImage . ')' );
        if( $this->present( $block, 'backgroundColor' ) ) $this->add_style( 'background-color', $block->backgroundColor );

        if( $this->present( $block, 'borderWidth' ) ) {
            $this->add_style( 'border-width', $block->borderWidth . 'px' );
            if( $this->present( $block, 'borderColor' ) ) $this->add_style( 'border-color', $block->borderColor );
            if( $this->present( $block, 'borderStyle' ) ) $this->add_style( 'border-style', $block->borderStyle );

            $this->add_style( 'border-width', $block->borderWidth . 'px', 'td' );
            if( $this->present( $block, 'borderColor' ) ) $this->add_style( 'border-color', $block->borderColor, 'td' );
            if( $this->present( $block, 'borderStyle' ) ) $this->add_style( 'border-style', $block->borderStyle, 'td' );
        }

        if( $this->present( $block, 'shadowColor' ) && $this->present( $block, 'shadowHorizontal' ) && $this->present( $block, 'shadowVertical' ) )
        {
            $blur = $this->present( $block, 'shadowBlur' ) ? $block->shadowBlur . 'px ' : ' ';
            $spread = $this->present( $block, 'shadowSpread' ) ? $block->shadowSpread . 'px ' : ' ';

            $shadow = $block->shadowHorizontal . 'px ' . $block->shadowVertical . 'px ' . $blur . $spread . $block->shadowColor . ' ' . $block->shadowType;

            $this->add_style( '-webkit-box-shadow', $shadow );
            $this->add_style( '-moz-box-shadow', $shadow );
            $this->add_style( 'box-shadow', $shadow );
        }

        // Text Style
        if( $this->present( $block, 'textAlign' ) && $this->type != 'container' ) {
            $this->add_style( 'text-align',   $block->textAlign );
            $this->add_style( 'text-align',   $block->textAlign, 'td' );
        }

        if( $this->present( $block, 'fontBold' ) && $block->fontBold ) {
            $this->add_style( 'font-weight',   'bold' );
            $this->add_style( 'font-weight',   'bold', 'td' );
        }
        if( $this->present( $block, 'fontSmallCaps' ) && $block->fontSmallCaps ) $this->add_style( 'font-variant',  'small-caps' );

        $fontSizeUnit   = $this->present( $block, 'fontSizeUnit' )   ? $block->fontSizeUnit : 'px';
        $lineHeightUnit = $this->present( $block, 'lineHeightUnit' ) ? $block->lineHeightUnit : 'px';

        if( $this->present( $block, 'fontSize' ) )   $this->add_style( 'font-size',   $block->fontSize . $fontSizeUnit );
        if( $this->present( $block, 'lineHeight' ) ) $this->add_style( 'line-height', $block->lineHeight . $lineHeightUnit );
        if( $this->present( $block, 'fontColor' ) )  $this->add_style( 'color',       $block->fontColor );
        if( $this->present( $block, 'linkColor' ) )  $this->link_color = $block->linkColor;

        if( $this->present( $block, 'fontFamilyType' ) && $block->fontFamilyType == 'manual' ) {
            if( $this->present( $block, 'fontFamilyManual' ) ) $this->add_style( 'font-family',       $block->fontFamilyManual );
        }
        if( $this->present( $block, 'fontFamilyType' ) && $block->fontFamilyType == 'gwf' ) {
            if( $this->present( $block, 'fontFamilyGWF' ) ) {
                $font = str_replace( '+',' ', $block->fontFamilyGWF );
                $font .= ', sans-serif';
                $this->add_style( 'font-family', $font );
            }
        }

        /*
         * Conditions
         */
        if( isset( $block->conditions ) ) {
            foreach( $block->conditions as $condition ) {
                if( $condition->condition_type == 'field' && $this->present( $condition, 'field' ) ) {
                    $this->add_condition( array( 'type' => 'hide', 'condition_type' => 'field', 'field' => $condition->field, 'when' => $condition->when ), $condition->target );
                } else if( $condition->condition_type == 'setting' && $this->present( $condition, 'setting' ) ) {
                    $this->add_condition( array( 'type' => 'hide', 'condition_type' => 'setting', 'setting' => $condition->setting, 'when' => $condition->when ), $condition->target );
                } else if( $condition->condition_type == 'responsive' ) {
                    $this->add_condition( array( 'type' => 'hide', 'condition_type' => 'responsive', 'when' => $condition->when ), $condition->target );
                }
            }
        }
    }

    protected function present( $block, $field )
    {
        if( is_array( $block ) ) {
            return isset( $block[$field] ) && !is_null( $block[$field] ) && $block[$field] != '';
        } else {
            return isset( $block->{$field} ) && !is_null( $block->{$field} ) && $block->{$field} != '';
        }
    }

    /*
     * Styling
     */

    public function add_style( $property, $value, $name = 'default' )
    {
        $this->style[$name][$property] = str_replace( '"', "'", $value );
    }

    private function get_style_string( $name )
    {
        $output = '';

        foreach( $this->style[$name] as $property => $value )
        {
            if( WPUltimateRecipe::option( 'recipe_template_force_style', '1' ) == '1' ) {
                $output .= $property . ':' . $value . ' !important;';
            } else {
                $output .= $property . ':' . $value . ';';
            }
        }

        return $output;
    }

    protected function style( $names = 'default' )
    {
        if( !is_array( $names ) ) {
            $names = array( $names );
        }

        $style = '';
        $class = '';

        if( in_array( 'default', $names ) ) {
            $class = ' class="wpurp-'.$this->type.'"';
        }

        foreach( $names as $name )
        {
            if( isset( $this->style[$name] ) ) {
                $style .= $this->get_style_string( $name );
            }
        }

        if( $style == '' ) {
            return $class;
        } else {
            return $class . ' style="' . $style . '"';
        }
    }

    /*
     * Conditions
     */

    public function add_condition( $condition, $target = 'block' )
    {
        if( $condition['condition_type'] == 'responsive' ) {
            if( $condition['when'] == 'mobile' ) {
                $this->show_on_mobile = false;
            } else if ( $condition['when'] == 'desktop' ) {
                $this->show_on_desktop = false;
            }
        }

        if( !isset( $this->conditions[$target] ) ) {
            $this->conditions[$target] = array();
        }

        $this->conditions[$target][] = $condition;
    }

    private function condition( $recipe, $condition )
    {
        $show = true;

        if( $condition['condition_type'] == 'field' ) {
            $present = $recipe->is_present( $condition['field'] );

            if( isset( $condition['when'] ) && $condition['when'] == 'present' ) {
                $show = $show && !$present; // Hide when present
            } else {
                $show = $show && $present; // Hide when missing
            }
        } else if( $condition['condition_type'] == 'setting' ) {
            $val = WPUltimateRecipe::option( $condition['setting'], '1' ); // TODO Only works for default 1 options at the moment

            if( $condition['setting'] == 'recipe_adjustable_units' && !WPUltimateRecipe::is_premium_active() ) {
                return false; // Hide unit conversion block if we're not Premium
            }

            if( isset( $condition['when'] ) && $condition['when'] == 'enabled' ) {
                $show = $show && $val != '1';
            } else {
                $show = $show && $val == '1';
            }
        }

        return $show;
    }

    protected function show( $recipe, $target = 'block' )
    {
        if( isset( $this->conditions[$target] ) ) {
            foreach( $this->conditions[$target] as $condition ) {
                if( !$this->condition( $recipe, $condition ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    /*
     * Output block, called before output of child.
     * Return false to not output the child.
     */
    protected function output_block( $recipe )
    {
        return $this->show( $recipe );
    }

    protected function before_output()
    {
        $output = '';

        // Responsive
        if( !$this->show_on_desktop ) {
            $output = '<div class="wpurp-responsive-mobile">';
        } else if( !$this->show_on_mobile ) {
            $output = '<div class="wpurp-responsive-desktop">';
        }

        // Background presets
        if( $this->background_preset )
        {
            switch( $this->background_preset ) {
                case 'default':
                    $img = WPUltimateRecipe::addon( 'custom-templates' )->addonUrl . '/img/default.png';
                    break;
                default:
                    $img = WPUltimateRecipe::addon( 'template-editor' )->addonUrl . '/img/' . $this->background_preset . '.png';
            }

            if( isset( $img ) ) $this->add_style( 'background', 'url(' . $img . ')' );
        }

        return $output;
    }

    protected function after_output( $output )
    {
        if( !$this->show_on_desktop || !$this->show_on_mobile ) {
            $output .= '</div>';
        }

        // TODO Better way of doing this?
        if( $this->link_color ) {
            preg_match_all("/<a [^><]*>/i", $output, $links);

            foreach( $links[0] as $link )
            {
                $new_link = preg_replace('/( style=")([^"]*")/i', '$1color: '.$this->link_color.' !important;$2', $link);

                if( $new_link == $link ) {
                    $new_link = str_ireplace('<a ', '<a style="color: '.$this->link_color.' !important;" ', $link);
                }

                $output = str_ireplace( $link, $new_link, $output );
            }
        }

        return apply_filters( 'wpurp_output_recipe_' .$this->type, $output );
    }

    /*
     * Quick Access
     */

    public function loc( $parent, $row, $column, $order )
    {
        $this->parent = $parent;
        $this->row = $row;
        $this->column = $column;
        $this->order = $order;

        return $this;
    }

    public function parent( $parent )
    {
        $this->parent = $parent;
        return $this;
    }

    public function row( $row )
    {
        $this->row = $row;
        return $this;
    }

    public function column( $column )
    {
        $this->column = $column;
        return $this;
    }

    public function order( $order )
    {
        $this->order = $order;
        return $this;
    }
}