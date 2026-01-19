<!--<ul class="links" block="block-languageswitcher">
<li hreflang="en" data-drupal-link-system-path="node/23" class="en is-active"><a href="<?php echo $this->base; ?>/en/products/product-database" class="language-link is-active" hreflang="en" data-drupal-link-system-path="node/23">English</a></li>
<li hreflang="de" data-drupal-link-system-path="node/23" class="de"><a href="<?php echo $this->base; ?>/de/products/product-database" class="language-link" hreflang="de" data-drupal-link-system-path="node/23">Deutsch</a></li>
<li hreflang="es" data-drupal-link-system-path="node/23" class="es"><a href="<?php echo $this->base; ?>/es/products/product-database" class="language-link" hreflang="es" data-drupal-link-system-path="node/23">Espanol</a></li>
<li hreflang="zh-hant" data-drupal-link-system-path="node/23" class="zh-hant"><a href="<?php echo $this->base; ?>/zh/products/product-database" class="language-link" hreflang="zh-hant" data-drupal-link-system-path="node/23">中文</a></li>
<li hreflang="fr" data-drupal-link-system-path="node/23" class="fr"><a href="<?php echo $this->base; ?>/fr/products/product-database" class="language-link" hreflang="fr" data-drupal-link-system-path="node/23">Francais</a></li>
<li hreflang="pt-br" data-drupal-link-system-path="node/23" class="pt-br"><a href="<?php echo $this->base; ?>/pt/products/product-database" class="language-link" hreflang="pt-br" data-drupal-link-system-path="node/23">Portuguese, Brazil</a></li>
<li hreflang="ja" data-drupal-link-system-path="node/23" class="ja"><a href="<?php echo $this->base; ?>/jp/products/product-database" class="language-link" hreflang="ja" data-drupal-link-system-path="node/23"></a>日本語</li>
<li hreflang="ko" data-drupal-link-system-path="node/23" class="ko"><a href="<?php echo $this->base; ?>/kr/products/product-database" class="language-link" hreflang="ko" data-drupal-link-system-path="node/23">Korean</a></li>
<li hreflang="pl" data-drupal-link-system-path="node/23" class="pl"><a href="<?php echo $this->base; ?>/pl/products/product-database" class="language-link" hreflang="ko" data-drupal-link-system-path="node/23">Pol</a></li>
<li hreflang="it" data-drupal-link-system-path="node/23" class="it"><a href="<?php echo $this->base; ?>/it/products/product-database" class="language-link" hreflang="ko" data-drupal-link-system-path="node/23">Ita</a></li>
</ul>-->


<script>
var str = '<li hreflang="pl" data-drupal-link-system-path="node/23" class="pl">' 
						+'<a href="http://www.kraiburg-tpe.com/neu/pl/product-database" class="language-link" hreflang="ko" data-drupal-link-system-path="node/23">Pol</a></li>'
						+ '<li hreflang="it" data-drupal-link-system-path="node/23" class="it">'
						+ '<a href="http://www.kraiburg-tpe.com/neu/it/node/57" class="language-link" hreflang="ko" data-drupal-link-system-path="node/23">Ita</a></li>';
	
	
	jQuery(document).ready(function(){						
		
		window.parent.jQuery("#block-languageswitcher").find('ul').append(str);
		
	});
	
</script>