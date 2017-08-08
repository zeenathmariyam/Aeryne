<?php
class Aeryne_Academy {
    public static function init() {
        add_shortcode('aeryne_academy', array(__CLASS__, 'shortcode'));

        add_action('after_setup_theme', function () {
            require_once 'register-types.php';

            add_post_type('student', __('Students', 'north'), __('Student', 'north'), array(
                    'public' => false,
                    'menu_position' => 51,
                    'supports' => array('title', 'editor', 'thumbnail', 'page-attributes')
                )
            );

            add_image_size('student-full', 1165, 1165);
            add_image_size('student', 369, 369);
        });
    }

    public static function shortcode($atts) {
        require_once 'WPQ.php';

        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'aeryne_academy');

        if (empty($atts['id']))
            return '';

        $earnings = self::formEarnings($atts['id']);
        $subGoal = WCoGive::subGoal();
        $color = get_post_meta($atts['id'], '_give_goal_color', true);
        $colorRGB = implode(',', array_map('hexdec', str_split(ltrim($color, '#'), 2)));

        if ($earnings === null)
            return '';

        $left = '';
        $right = '';
        $inner = '';
        $bottom = '';

        $main = '
        <div class="aeryne-academy">
            <div class="small-12 large-9 columns">%s</div>
            <div class="small-12 large-3 columns">%s</div>
            <div class="small-12 columns">%s</div>
        </div>';

        // LEFT
        $rowFormat = '
        <h3>%s</h3>
        <div class="row">
            %s
        </div>';

        $colFormat = '
        <div class="%4$saeryne-academy">
            <div class="image">%2$s</div>
            %3$s
            %1$s
        </div>
        ';

        $textFormat = '<h3>%s</h3><p>%s</p>';

        $students = WPQ::get(array(
            'post_type' => 'student',
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'suppress_filters' => false
        ));

        $currentEarnings = 0;

        for ($i = 0; $i < count($students); $i++) {
            $student = $students[$i];

            $c = $earnings - ($subGoal * $i);
            $p = $c/$subGoal;
            $pc = round($p * 100, 3);

            $name = apply_filters('the_title', $student->post_title, $student->ID);
            $image = get_post_thumbnail_id($student->ID);

            $style = '';//'box-shadow:0 0 5px 5px rgba('.$colorRGB.','.$p.')';

            if ($p >= 1)
                $inner .= sprintf(
                    $colFormat,
                    $name,
                    wp_get_attachment_image($image, 'student', false, array('style' => $style)),
                    '',//self::_progressBar($pc, $color),
                    'small-3 columns '
                );
            else if (!$left) {
                $currentEarnings = $c;
                $left = sprintf(
                    $colFormat,
                    sprintf($textFormat, $name, self::_filterContent($student->post_content)),
                    wp_get_attachment_image($image, 'student-full', false, array('style' => $style)),
                    '',//self::_progressBar($pc, $color),
                    ''
                );
            }
        }

        ob_start();
        woocommerce_product_loop_start();

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'id',
                    'terms'    => WCoGive::category()
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );

        while (WPQ::loop($args)) {
            wc_get_template_part('content', 'product');
        }

        woocommerce_product_loop_end();
        $products = ob_get_clean();

        if ($products)
            $bottom .= $products;

        if ($inner)
            $bottom .= sprintf($rowFormat, __('Funded students', 'north'), $inner);

        $right .= self::_progress($currentEarnings, $subGoal, $color);
        $right .= do_shortcode('[give_form id='.$atts['id'].' show_content=none display_style=reveal show_title=false]');

        return sprintf($main, $left, $right, $bottom);
    }

    public static function formEarnings($formID) {
        $form = new Give_Donate_Form($formID);

        if (!$form)
            return null;

        return $form->get_earnings();
    }

    public static function formProgress($formID) {
        $form = new Give_Donate_Form($formID);

        if (!$form)
            return null;

        $goal = $form->goal;
        $income = $form->get_earnings();

        return $income / $goal;
    }

    private static function _progress($income, $goal, $color) {
        $progress = round($income/$goal * 100, 3);

        $format = '
        <div class="give-goal-progress">
            <div class="raised">%s</div>
            %s
        </div>';

        $income = give_human_format_large_amount( give_format_amount( $income ) );
        $goal = give_human_format_large_amount( give_format_amount( $goal ) );

        $raised = sprintf(
        /* translators: 1: amount of income raised 2: goal target ammount */
            __( '%1$s of %2$s raised', 'give' ),
            '<span class="income">' . apply_filters( 'give_goal_amount_raised_output', give_currency_filter( $income ) ) . '</span>',
            '<span class="goal-text">' . apply_filters( 'give_goal_amount_target_output', give_currency_filter( $goal ) ) . '</span>'
        );

        $progress = self::_progressBar($progress, $color);

        return sprintf($format, $raised, $progress);
    }

    private static function _progressBar($percent, $color) {
        return sprintf('
        <div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%1$s">
            <span style="width: %1$s%%;background-color:%2$s"></span>
        </div>', $percent, $color);
    }

    private static function _filterContent($content) {
        return str_replace(']]>', ']]&gt;', apply_filters('the_content', $content));
    }
}

Aeryne_Academy::init();