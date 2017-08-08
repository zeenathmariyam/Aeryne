<div class="locale-overlay">
    <nav class="locale-window">
        <h1>Choose your store</h1>
        <hr>
        <div class="locale-row">
            <div class="locale-europe small-12 medium-6 large-3 columns">
                <h2>Europe</h2>
                <ul>
                    <?php
                    $options = array(
                        'Austria | Österreich' => 'xx',
                        'Belgium | België | Belgique' => 'xx',
                        'Bulgaria | България' => 'xx',
                        'Croatia' => 'xx',
                        'Cyprus' => 'xx',
                        'Czech Republic | Česká republika' => 'xx',
                        'Denmark | Danmark' => 'xx',
                        'Estonia' => 'xx',
                        'Finland | Suomi' => 'fi',
                        'France' => 'fr',
                        'Germany | Deutschland' => 'xx',
                        'Greece' => 'xx',
                        'Hungary | Magyarország' => 'xx',
                        'Ireland' => 'xx',
                        'Italy | Italia' => 'xx',
                        'Latvia' => 'xx',
                    );
                foreach ($options as $label => $id) {
                    if ($locale = WCo_Locale::getLocale($id))
                        echo $locale->render($label);
                }?>
                </ul>
            </div>
            <div class="locale-europe-2 small-12 medium-6 large-3 columns">
                <h2>Europe</h2>
                <ul>
                    <?php
                    $options = array(
                        'Lithuania' => 'xx',
                        'Luxembourg' => 'xx',
                        'Netherlands | Nederland' => 'xx',
                        'Norway | Norge' => 'no',
                        'Poland | Polska' => 'xx',
                        'Portugal' => 'xx',
                        'Romania | România' => 'xx',
                        'Serbia' => 'xx',
                        'Slovakia | Slovenská republika' => 'xx',
                        'Slovenia' => 'xx',
                        'Spain | España' => 'xx',
                        'Sweden | Sverige' => 'sv',
                        'Switzerland | Schweiz | Svizzera | Suisse' => 'xx',
                        'Turkey | Türkiye' => 'xx',
                        'United Kingdom' => 'xx',
                    );
                foreach ($options as $label => $id) {
                    if ($locale = WCo_Locale::getLocale($id))
                        echo $locale->render($label);
                }?>
                </ul>
            </div>
            <div class="locale-asia small-12 medium-6 large-3 columns">
                <h2>Asia Pacific</h2>
                <ul>
                    <?php
                    $options = array(
                        'Australia' => 'xx',
                        'China | 中国' => 'xx',
                        'Hong Kong SAR' => 'xx',
                        'India' => 'xx',
                        'Indonesia' => 'xx',
                        'Japan | 日本' => 'xx',
                        'Macau | 澳門' => 'xx',
                        'Malaysia' => 'xx',
                        'New Zealand' => 'xx',
                        'Philippines' => 'xx',
                        'Singapore' => 'xx',
                        'South Korea ' => 'xx',
                        'Taiwan | 台灣' => 'xx',
                        'Thailand ' => 'xx',
                    );
                    foreach ($options as $label => $id)
                        echo WCo_Locale::getLocale($id)->render($label);?>
                </ul>
            </div>
            <div class="locale-other small-12 medium-6 large-3 columns">
                <h2>North and South America</h2>
                <ul>
                    <?php
                    $options = array(
                        'Canada' => 'xx',
                        'Chile' => 'xx',
                        'Mexico | México' => 'xx',
                        'Peru | Perú' => 'xx',
                        'United States' => 'xx',
                    );
                foreach ($options as $label => $id) {
                    if ($locale = WCo_Locale::getLocale($id))
                        echo $locale->render($label);
                }?>
                </ul>
                <h2>Middle East</h2>
                <ul>
                    <?php
                    $options = array(
                        'Bahrain | البحرين' => 'xx',
                        'Israel | ישראל' => 'xx',
                        'Jordan | الأردن' => 'xx',
                        'Kuwait | الكويت' => 'xx',
                        'Lebanon | لبنان' => 'xx',
                        'Oman | سلطنة عمان' => 'xx',
                        'Qatar | قطر' => 'xx',
                        'Saudi Arabia | السعودية' => 'xx',
                        'United Arab Emirates | الإمارات' => 'xx',
                    );
                foreach ($options as $label => $id) {
                    if ($locale = WCo_Locale::getLocale($id))
                        echo $locale->render($label);
                }?>
                </ul>
            </div>
        </div>
    </nav>
</div>