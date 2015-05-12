<?php

class Tag_Manager
{
	public static function booking() {
        global $wp_simpelreserveren;
		?>
        <script>
            dataLayer = [{
                'transactionId': '<?php echo $wp_simpelreserveren->boeken['boeken_id'] ?>',
                'transactionAffiliation': '<?php echo $wp_simpelreserveren->accommodatie->title ?>',
                'transactionTotal': <?php echo $wp_simpelreserveren->boeken['totaal'] ?>,
                'transactionTax': 0,
                'transactionShipping': 0,
                'transactionProducts': [{
                    'sku': '<?php echo $wp_simpelreserveren->accommodatie->id ?>',
                    'name': '<?php echo $wp_simpelreserveren->accommodatie->title ?>',
                    'category': '<?php echo $wp_simpelreserveren->accommodatie->type->title ?>',
                    'price': <?php echo $wp_simpelreserveren->boeken['totaal'] ?>,
                    'quantity': 1
                }]
            }];

            dataLayer.push({
              'ecommerce': {
                'purchase': {
                  'actionField': {
                    'id': '<?php echo $wp_simpelreserveren->boeken['boeken_id'] ?>',                         // Transaction ID. Required for purchases and refunds.
                    'affiliation': '<?php echo $wp_simpelreserveren->accommodatie->title ?>',
                    'revenue': '<?php echo $wp_simpelreserveren->boeken['totaal'] ?>',                     // Total transaction value (incl. tax and shipping)
                    'tax':'0',
                    'shipping': '0',
                    'coupon': ''
                  },
                  'products': [{                            // List of productFieldObjects.
                    'name': '<?php echo $wp_simpelreserveren->accommodatie->title ?>',     // Name or ID is required.
                    'id': '<?php echo $wp_simpelreserveren->accommodatie->id ?>',
                    'price': '<?php echo $wp_simpelreserveren->accommodatie_prijs ?>',
                    'brand': 'Simpel Reserveren',
                    'category': '<?php echo $wp_simpelreserveren->accommodatie->type->title ?>',
                    'variant': '',
                    'quantity': 1,
                    'coupon': ''                            // Optional fields may be omitted or set to empty string.
                   },
                    <?php foreach($wp_simpelreserveren->toeslagen as $toeslag) : ?>
                    {                            
                    'name': '<?php echo $toeslag->title ?>',     // Name or ID is required.
                    'id': '<?php echo $toeslag->id ?>',
                    'price': '<?php echo ($toeslag->type == 'aantal' ? ($toeslag->totaal_prijs / $toeslag->aantal) : $toeslag->totaal_prijs) ?>',
                    'brand': 'Simpel Reserveren',
                    'category': 'Toeslagen',
                    'variant': '',
                    'quantity': <?= ($toeslag->type == 'aantal' ? $toeslag->aantal : 1) ?>,
                    'coupon': ''                            // Optional fields may be omitted or set to empty string.
                   },
                    <?php endforeach; ?>
                   ]
                }
              }
            });

        </script>
        <?php
	}

    public static function view() {
        global $wp_simpelreserveren;
        ?>
        <script>
            dataLayer = [];
            dataLayer.push({
              'ecommerce': {
                'detail': {
                  'products': [{
                    'name': '<?= $wp_simpelreserveren->accommodatie->title ?>',         // Name or ID is required.
                    'id': '<?= $wp_simpelreserveren->accommodatie->id ?>',
                    'price': '<?= $wp_simpelreserveren->accommodatie->vanaf_prijs ?>',
                    'brand': 'Simpel Reserveren',
                    'category': '<?= $wp_simpelreserveren->accommodatie->type->title ?>',
                    'variant': ''
                   }]
                 }
               }
            });            
        </script>
        <?php
    }    

    public static function search() {
        global $wp_simpelreserveren;
        $i = 1;
        ?>
        <script>
            dataLayer = [];
            dataLayer.push({
              'ecommerce': {
                'currencyCode': 'EUR',                       // Local currency is optional.
                'impressions': [
     
                <?php foreach($wp_simpelreserveren->results as $accommodatie) : ?>
                    {
                       'name': '<?= $accommodatie->title ?>',       // Name or ID is required.
                       'id': '<?= $accommodatie->id ?>',
                       'price': '<?= $accommodatie->prijs ?>',
                       'brand': 'Simpel Reserveren',
                       'category': '<?= $accommodatie->type->title ?>',
                       'variant': '',
                       'list': '',
                       'position': '<?= $i++ ?>'
                     },
                <?php endforeach; ?>
                ]
               }
            });            
        </script>
        <?php
    }

    public static function checkout() {
        global $wp_simpelreserveren;
        ?>
        <script>
            dataLayer = [];
            dataLayer.push({
                'event': 'checkout',
                'ecommerce': {
                  'checkout': {
                    'actionField': {'step': <?=$wp_simpelreserveren->stap ?>},
                    'products': [{
                      'name': '<?= $wp_simpelreserveren->accommodatie->title ?>',
                      'id': '<?= $wp_simpelreserveren->accommodatie->id ?>',
                      'price': '<?= $_SESSION['boeken']['totaal'] ?>',
                      'brand': 'Simpel Reserveren',
                      'category': '<?= $wp_simpelreserveren->accommodatie->type->title ?>',
                      'variant': '',
                      'quantity': 1
                   }]
                 }
               }
            });            
        </script>
        <?php
    }
}