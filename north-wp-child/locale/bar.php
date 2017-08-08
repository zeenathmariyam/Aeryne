<?php
$options = WCo_Locale::getLocales();
$current = WCo_Locale::currentLocale();

$texts = array(
    'xx' => 'Free shipping worldwide on orders over €149',
    'sv' => 'Fri frakt inom Sverige på ordrar över 500 kr',
    'fi' => 'Free shipping on orders over €149 to Finland',
    'no' => 'Free shipping on orders over 1999 kr to Norway',
    'fr' => '',
);

$data = array();
foreach ($options as $locale)
    $data[$locale->id] = $locale->currentURL;
?>
<div class="locale-bar" data-locales="<?php echo esc_attr(json_encode($data));?>">
    <div class="current"><?php echo $texts[$current->id]?></div>
    <nav class="locale-list">
        <h1><?php _e('Change store', THEME_TEXT);?></h1>
        <ul>
            <?php
            foreach ($options as $locale)
                echo $locale->render();
            ?>
        </ul>
    </nav>
</div>