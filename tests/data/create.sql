
DROP TABLE IF EXISTS `wptests_mv_creations`;
CREATE TABLE `wptests_mv_creations` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `object_id` bigint DEFAULT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `author` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `instructions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `instructions_with_ads` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `schema_display` tinyint(1) NOT NULL DEFAULT '1',
  `published` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `suitable_for_diet` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `difficulty` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `estimated_cost` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `original_post_id` bigint DEFAULT NULL,
  `canonical_post_id` bigint DEFAULT NULL,
  `category` bigint DEFAULT NULL,
  `secondary_term` bigint DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `thumbnail_id` bigint DEFAULT NULL,
  `prep_time` bigint DEFAULT NULL,
  `active_time` bigint DEFAULT NULL,
  `additional_time` bigint DEFAULT NULL,
  `total_time` bigint DEFAULT NULL,
  `prep_time_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Prep Time',
  `active_time_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Active Time',
  `additional_time_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Additional Time',
  `time_display` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `yield` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `pinterest_url` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `pinterest_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `pinterest_img_id` bigint DEFAULT NULL,
  `video` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `external_video` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `associated_posts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `rating` float(2,1) DEFAULT NULL,
  `rating_count` int DEFAULT NULL,
  `json_ld` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `custom_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `original_object_id` bigint DEFAULT NULL,
  `layout` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `title_hide` tinyint(1) NOT NULL DEFAULT '0',
  `description_hide` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `object_id` (`object_id`),
  UNIQUE KEY `original_object_id` (`original_object_id`),
  KEY `original_post_id` (`original_post_id`),
  KEY `canonical_post_id` (`canonical_post_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
LOCK TABLES `wptests_mv_creations` WRITE;
/*!40000 ALTER TABLE `wptests_mv_creations` DISABLE KEYS */;

INSERT INTO `wptests_mv_creations` (`id`, `created`, `modified`, `object_id`, `type`, `title`, `author`, `description`, `instructions`, `instructions_with_ads`, `notes`, `schema_display`, `published`, `suitable_for_diet`, `difficulty`, `estimated_cost`, `original_post_id`, `canonical_post_id`, `category`, `secondary_term`, `tags`, `thumbnail_id`, `prep_time`, `active_time`, `additional_time`, `total_time`, `prep_time_label`, `active_time_label`, `additional_time_label`, `time_display`, `yield`, `pinterest_url`, `pinterest_description`, `pinterest_img_id`, `video`, `external_video`, `keywords`, `associated_posts`, `rating`, `rating_count`, `json_ld`, `metadata`, `custom_fields`, `original_object_id`, `layout`, `title_hide`, `description_hide`)
VALUES
  (13, '2021-03-30 22:31:25', '2021-04-20 18:53:24', 1270, 'recipe', 'Oatmilk Honey Latte', 'Katie Koteen', '<p>Oatmilk <a href=\"https://www.amazon.com/gp/product/B07MQPXP7D/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1\">Honey</a> Latte – a delicious homemade morning treat! Steaming hot, rich, and dark coffee that’s satisfyingly creamy, barely sweet, and warm-spiced.</p>', '<ol><ol><li>[mv_schema_meta name=&quot;Pour the milk&quot;], <a href=\"https://www.amazon.com/gp/product/B07MQPXP7D/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1\">honey</a>, and salt into a <a href=\"https://amzn.to/2N1QlJ2\">glass jar</a>. Microwave for about a minute, until very hot.</li><li>[mv_schema_meta name=&quot;Put a cap on the jar&quot;]<strong>:</strong> and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.[mv_schema_meta name=&quot;Pour your milky mixture&quot;]</li><li> into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.</li></ol></ol>', '<ol><ol><li>[mv_schema_meta name=&quot;Pour the milk&quot;], <a href=\"https://www.amazon.com/gp/product/B07MQPXP7D/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1\">honey</a>, and salt into a <a href=\"https://amzn.to/2N1QlJ2\">glass jar</a>. Microwave for about a minute, until very hot.</li><li>[mv_schema_meta name=&quot;Put a cap on the jar&quot;]<strong>:</strong> and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.[mv_schema_meta name=&quot;Pour your milky mixture&quot;]</li><li> into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.</li></ol></ol>', '<p>And this is a note. </p>', 1, '{\"id\":\"13\",\"created\":\"2021-03-30 22:31:25\",\"modified\":\"2021-04-16 18:26:18\",\"object_id\":\"1270\",\"type\":\"recipe\",\"title\":\"Oatmilk Honey Latte\",\"author\":\"Katie Koteen\",\"description\":\"<p>Oatmilk\\u00a0<a href=\\\"https:\\/\\/www.amazon.com\\/gp\\/product\\/B07MQPXP7D\\/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1\\\">Honey<\\/a>\\u00a0Latte \\u2013 a delicious homemade morning treat! Steaming hot, rich, and dark coffee that\\u2019s satisfyingly creamy, barely sweet, and warm-spiced.<\\/p>\",\"instructions\":\"<ol><ol><li id=\\\"mv_create_13_1\\\">[mv_schema_meta name=\\\"Pour the milk\\\"],&nbsp;<a href=\\\"https:\\/\\/www.amazon.com\\/gp\\/product\\/B07MQPXP7D\\/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1\\\">honey<\\/a>, and salt into a&nbsp;<a href=\\\"https:\\/\\/amzn.to\\/2N1QlJ2\\\">glass jar<\\/a>. Microwave for about a minute, until very hot.<\\/li><li id=\\\"mv_create_13_2\\\">[mv_schema_meta name=\\\"Put a cap on the jar\\\"]<strong>:<\\/strong>&nbsp;and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.[mv_schema_meta name=\\\"Pour your milky mixture\\\"]<\\/li><li id=\\\"mv_create_13_3\\\">&nbsp;into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.<\\/li><\\/ol><\\/ol>\",\"instructions_with_ads\":\"<ol><ol><li>[mv_schema_meta name=&quot;Pour the milk&quot;],\\u00a0<a href=\\\"https:\\/\\/www.amazon.com\\/gp\\/product\\/B07MQPXP7D\\/ref=ppx_yo_dt_b_asin_title_o02_s00?ie=UTF8&amp;psc=1\\\">honey<\\/a>, and salt into a\\u00a0<a href=\\\"https:\\/\\/amzn.to\\/2N1QlJ2\\\">glass jar<\\/a>. Microwave for about a minute, until very hot.<\\/li><li>[mv_schema_meta name=&quot;Put a cap on the jar&quot;]<strong>:<\\/strong>\\u00a0and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.[mv_schema_meta name=&quot;Pour your milky mixture&quot;]<\\/li><li>\\u00a0into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.<\\/li><\\/ol><\\/ol>\",\"notes\":\"<p>And this is a note. <\\/p>\",\"schema_display\":\"1\",\"suitable_for_diet\":\"VegetarianDiet\",\"difficulty\":null,\"estimated_cost\":null,\"original_post_id\":\"1288\",\"canonical_post_id\":\"1288\",\"category\":null,\"secondary_term\":\"30\",\"tags\":null,\"thumbnail_id\":\"994\",\"prep_time\":{\"original\":300,\"years\":0,\"months\":0,\"days\":0,\"hours\":0,\"minutes\":5,\"seconds\":0},\"active_time\":{\"original\":600,\"years\":0,\"months\":0,\"days\":0,\"hours\":0,\"minutes\":10,\"seconds\":0},\"additional_time\":null,\"total_time\":{\"original\":900,\"years\":0,\"months\":0,\"days\":0,\"hours\":0,\"minutes\":15,\"seconds\":0},\"prep_time_label\":\"Prep Time\",\"active_time_label\":\"Cook Time\",\"additional_time_label\":\"Additional Time\",\"time_display\":\"prep_time,active_time,additional_time\",\"yield\":\"1 serving\",\"pinterest_url\":null,\"pinterest_description\":null,\"pinterest_img_id\":0,\"video\":null,\"external_video\":\"{\\\"id\\\":\\\"tjaY2r6XF5w\\\",\\\"name\\\":\\\"Spicy Peanut Soba Noodle Salad\\\",\\\"description\\\":\\\"This Spicy Peanut Soba Noodle Salad features red peppers, cabbage, chicken, soba noodles, and a quick homemade spicy peanut sauce. Salads don\\u2019t get much yummier than this.\\\\n\\\\nFull recipe: https:\\/\\/pinchofyum.com\\/spicy-peanut-soba-noodle-salad\\\\n\\\\nInstagram: https:\\/\\/www.instagram.com\\/pinchofyum\\/\\\\nFacebook: https:\\/\\/www.facebook.com\\/pinchofyum\\/\\\\nTwitter: https:\\/\\/twitter.com\\/pinchofyum\\\\nPinterest: https:\\/\\/www.pinterest.com\\/pinchofyum\\/\\\",\\\"duration\\\":\\\"PT1M3S\\\",\\\"thumbnailUrl\\\":\\\"https:\\/\\/i.ytimg.com\\/vi\\/tjaY2r6XF5w\\/hqdefault.jpg\\\",\\\"uploadDate\\\":\\\"2021-03-23T14:26:41Z\\\",\\\"contentUrl\\\":\\\"https:\\/\\/www.youtube.com\\/watch?v=tjaY2r6XF5w\\\",\\\"display\\\":true,\\\"source\\\":\\\"YOUTUBE\\\"}\",\"keywords\":\"coffee, drink, honey\",\"associated_posts\":\"[\\\"1288\\\"]\",\"rating\":4.5,\"rating_count\":4,\"json_ld\":\"{\\\"@context\\\":\\\"http:\\\\\\/\\\\\\/schema.org\\\",\\\"@type\\\":\\\"Recipe\\\",\\\"name\\\":\\\"Oatmilk Honey Latte\\\",\\\"author\\\":{\\\"@type\\\":\\\"Person\\\",\\\"name\\\":\\\"Katie Koteen\\\"},\\\"datePublished\\\":\\\"2021-03-30\\\",\\\"recipeYield\\\":1,\\\"description\\\":\\\"Oatmilk\\\\u00a0Honey\\\\u00a0Latte \\\\u2013 a delicious homemade morning treat! Steaming hot, rich, and dark coffee that\\\\u2019s satisfyingly creamy, barely sweet, and warm-spiced.\\\",\\\"image\\\":[\\\"https:\\\\\\/\\\\\\/demo.wptasty.com\\\\\\/wp-content\\\\\\/uploads\\\\\\/2020\\\\\\/11\\\\\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\\\",\\\"https:\\\\\\/\\\\\\/demo.wptasty.com\\\\\\/wp-content\\\\\\/uploads\\\\\\/2020\\\\\\/11\\\\\\/Honey-Oatmilk-Latte-Square-185x185-1-185x180.jpg\\\"],\\\"recipeCuisine\\\":\\\"american\\\",\\\"prepTime\\\":\\\"PT5M\\\",\\\"cookTime\\\":\\\"PT10M\\\",\\\"performTime\\\":\\\"PT10M\\\",\\\"totalTime\\\":\\\"PT15M\\\",\\\"recipeIngredient\\\":[\\\"3\\\\\\/4 cup oatmilk\\\",\\\"1\\\\u20132 tablespoons honey (depends how sweet you like it)\\\",\\\"1 tiny tiny pinch of salt\\\",\\\"1\\\\\\/2 cup of strongly brewed coffee (decaf, in my case!)\\\",\\\"a dusting of cinnamon, optional\\\"],\\\"recipeInstructions\\\":[{\\\"@type\\\":\\\"HowToStep\\\",\\\"text\\\":\\\",\\\\u00a0honey, and salt into a\\\\u00a0glass jar. Microwave for about a minute, until very hot.\\\",\\\"position\\\":1,\\\"name\\\":\\\"Pour the milk\\\",\\\"url\\\":\\\"https:\\\\\\/\\\\\\/demo.wptasty.com\\\\\\/2021\\\\\\/04\\\\\\/06\\\\\\/1288\\\\\\/#mv_create_13_1\\\"},{\\\"@type\\\":\\\"HowToStep\\\",\\\"text\\\":\\\":\\\\u00a0and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.\\\",\\\"position\\\":2,\\\"name\\\":\\\"Put a cap on the jar\\\",\\\"url\\\":\\\"https:\\\\\\/\\\\\\/demo.wptasty.com\\\\\\/2021\\\\\\/04\\\\\\/06\\\\\\/1288\\\\\\/#mv_create_13_2\\\"},{\\\"@type\\\":\\\"HowToStep\\\",\\\"text\\\":\\\"\\\\u00a0into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.\\\",\\\"position\\\":3,\\\"name\\\":\\\"\\\\u00a0into a mug. Add coffee. Sprinkle with cinnamon....\\\",\\\"url\\\":\\\"https:\\\\\\/\\\\\\/demo.wptasty.com\\\\\\/2021\\\\\\/04\\\\\\/06\\\\\\/1288\\\\\\/#mv_create_13_3\\\"}],\\\"video\\\":{\\\"@type\\\":\\\"VideoObject\\\",\\\"name\\\":\\\"Spicy Peanut Soba Noodle Salad\\\",\\\"description\\\":\\\"This Spicy Peanut Soba Noodle Salad features red peppers, cabbage, chicken, soba noodles, and a quick homemade spicy peanut sauce. Salads don\\\\u2019t get much yummier than this.\\\\n\\\\nFull recipe: https:\\\\\\/\\\\\\/pinchofyum.com\\\\\\/spicy-peanut-soba-noodle-salad\\\\n\\\\nInstagram: https:\\\\\\/\\\\\\/www.instagram.com\\\\\\/pinchofyum\\\\\\/\\\\nFacebook: https:\\\\\\/\\\\\\/www.facebook.com\\\\\\/pinchofyum\\\\\\/\\\\nTwitter: https:\\\\\\/\\\\\\/twitter.com\\\\\\/pinchofyum\\\\nPinterest: https:\\\\\\/\\\\\\/www.pinterest.com\\\\\\/pinchofyum\\\\\\/\\\",\\\"thumbnailUrl\\\":\\\"https:\\\\\\/\\\\\\/i.ytimg.com\\\\\\/vi\\\\\\/tjaY2r6XF5w\\\\\\/hqdefault.jpg\\\",\\\"contentUrl\\\":\\\"https:\\\\\\/\\\\\\/www.youtube.com\\\\\\/watch?v=tjaY2r6XF5w\\\",\\\"duration\\\":\\\"PT1M3S\\\",\\\"uploadDate\\\":\\\"2021-03-23T14:26:41Z\\\"},\\\"keywords\\\":\\\"coffee, drink, honey\\\",\\\"suitableForDiet\\\":\\\"VegetarianDiet\\\",\\\"nutrition\\\":{\\\"@type\\\":\\\"NutritionInformation\\\",\\\"calories\\\":\\\"320 calories\\\",\\\"fatContent\\\":\\\"2 grams fat\\\",\\\"servingSize\\\":\\\"8 oz.\\\"},\\\"url\\\":\\\"https:\\\\\\/\\\\\\/demo.wptasty.com\\\\\\/2021\\\\\\/04\\\\\\/06\\\\\\/1288\\\\\\/\\\"}\",\"metadata\":\"{\\\"slug_repaired\\\":true,\\\"fixed_associated_posts_column\\\":true,\\\"revisions_removed\\\":true}\",\"custom_fields\":\"[]\",\"original_object_id\":null,\"layout\":null,\"title_hide\":\"0\",\"description_hide\":\"0\",\"nutrition\":{\"id\":\"5\",\"created\":\"2021-03-30 22:37:30\",\"modified\":\"2021-03-30 22:39:40\",\"creation\":\"13\",\"serving_size\":\"8 oz.\",\"number_of_servings\":\"1\",\"calories\":\"320\",\"total_fat\":\"2\",\"saturated_fat\":\"\",\"trans_fat\":\"\",\"unsaturated_fat\":\"\",\"cholesterol\":\"\",\"sodium\":\"\",\"carbohydrates\":\"\",\"fiber\":\"\",\"sugar\":\"\",\"protein\":\"\",\"net_carbs\":\"\",\"sugar_alcohols\":\"\",\"calculated\":\"0000-00-00 00:00:00\",\"source\":\"\",\"display_zeros\":\"\"},\"products\":[],\"thumbnail_uri\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"category_name\":\"\",\"secondary_term_name\":\"american\",\"posts\":[{\"id\":\"1288\",\"title\":\"Private: Amber Testing\"}],\"ingredients\":{\"mv-has-no-group\":[{\"id\":\"238\",\"created\":\"2021-03-30 22:32:10\",\"modified\":\"2021-03-30 22:32:10\",\"type\":\"ingredients\",\"creation\":\"13\",\"original_text\":\"3\\/4 cup oatmilk\",\"note\":null,\"link\":null,\"group\":\"mv-has-no-group\",\"position\":\"0\",\"amount\":\"4\",\"max_amount\":null,\"nofollow\":\"1\"},{\"id\":\"239\",\"created\":\"2021-03-30 22:32:10\",\"modified\":\"2021-03-30 22:32:10\",\"type\":\"ingredients\",\"creation\":\"13\",\"original_text\":\"1\\u20132 tablespoons honey (depends how sweet you like it)\",\"note\":\"depends how sweet you like it\",\"link\":null,\"group\":\"mv-has-no-group\",\"position\":\"1\",\"amount\":\"1\",\"max_amount\":\"2\",\"nofollow\":\"1\"},{\"id\":\"240\",\"created\":\"2021-03-30 22:32:10\",\"modified\":\"2021-03-30 22:32:10\",\"type\":\"ingredients\",\"creation\":\"13\",\"original_text\":\"1 tiny tiny pinch of salt\",\"note\":null,\"link\":null,\"group\":\"mv-has-no-group\",\"position\":\"2\",\"amount\":\"1\",\"max_amount\":null,\"nofollow\":\"1\"},{\"id\":\"241\",\"created\":\"2021-03-30 22:32:10\",\"modified\":\"2021-03-30 22:32:10\",\"type\":\"ingredients\",\"creation\":\"13\",\"original_text\":\"1\\/2 cup of strongly brewed coffee (decaf, in my case!)\",\"note\":\"in my case!)\",\"link\":null,\"group\":\"mv-has-no-group\",\"position\":\"3\",\"amount\":\"0.5\",\"max_amount\":null,\"nofollow\":\"1\"},{\"id\":\"242\",\"created\":\"2021-03-30 22:32:10\",\"modified\":\"2021-03-30 22:32:10\",\"type\":\"ingredients\",\"creation\":\"13\",\"original_text\":\"a dusting of cinnamon, optional\",\"note\":\"optional\",\"link\":null,\"group\":\"mv-has-no-group\",\"position\":\"4\",\"amount\":null,\"max_amount\":null,\"nofollow\":\"1\"}]},\"images\":{\"mv_create_1x1\":{\"id\":\"49\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_1x1\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_1x1_medium_res\":{\"id\":\"50\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_1x1_medium_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_1x1_medium_high_res\":{\"id\":\"51\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_1x1_medium_high_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_1x1_high_res\":{\"id\":\"52\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_1x1_high_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_4x3\":{\"id\":\"53\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_4x3\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_4x3_medium_res\":{\"id\":\"54\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_4x3_medium_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_4x3_high_res\":{\"id\":\"55\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_4x3_high_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_16x9\":{\"id\":\"56\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_16x9\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-185x180.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"\",\"image_srcset_sizes\":\"\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_16x9_medium_res\":{\"id\":\"57\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_16x9_medium_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_16x9_high_res\":{\"id\":\"58\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_16x9_high_res\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_no_ratio\":{\"id\":\"59\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_no_ratio\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"},\"mv_create_vert\":{\"id\":\"60\",\"type\":\"image\",\"object_id\":\"994\",\"image_size\":\"mv_create_vert\",\"image_url\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_url_full_size\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"image_srcset\":\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg 185w, https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-151x151.jpg 151w\",\"image_srcset_sizes\":\"(max-width: 185px) 100vw, 185px\",\"associated_id\":\"13\",\"associated_type\":\"recipe\",\"created\":\"2021-03-30 22:32:49\",\"modified\":\"2021-04-16 18:26:18\"}},\"create_settings\":{\"mv_create_copyright_attribution\":\"Demo Robot\",\"mv_create_affiliate_message\":\"As an Amazon Associate and member of other affiliate programs, I earn from qualifying purchases.\"}}', 'VegetarianDiet', NULL, NULL, 1288, NULL, NULL, 30, NULL, 994, 300, 600, NULL, 900, 'Prep Time', 'Cook Time', 'Additional Time', 'prep_time,active_time,additional_time', '1 serving', NULL, NULL, NULL, NULL, '{\"id\":\"tjaY2r6XF5w\",\"name\":\"Spicy Peanut Soba Noodle Salad\",\"description\":\"This Spicy Peanut Soba Noodle Salad features red peppers, cabbage, chicken, soba noodles, and a quick homemade spicy peanut sauce. Salads don’t get much yummier than this.\\n\\nFull recipe: https://pinchofyum.com/spicy-peanut-soba-noodle-salad\\n\\nInstagram: https://www.instagram.com/pinchofyum/\\nFacebook: https://www.facebook.com/pinchofyum/\\nTwitter: https://twitter.com/pinchofyum\\nPinterest: https://www.pinterest.com/pinchofyum/\",\"duration\":\"PT1M3S\",\"thumbnailUrl\":\"https://i.ytimg.com/vi/tjaY2r6XF5w/hqdefault.jpg\",\"uploadDate\":\"2021-03-23T14:26:41Z\",\"contentUrl\":\"https://www.youtube.com/watch?v=tjaY2r6XF5w\",\"display\":true,\"source\":\"YOUTUBE\"}', 'coffee, drink, honey', NULL, '4.5', '4', '{\"@context\":\"http:\\/\\/schema.org\",\"@type\":\"Recipe\",\"name\":\"Oatmilk Honey Latte\",\"author\":{\"@type\":\"Person\",\"name\":\"Katie Koteen\"},\"datePublished\":\"2021-03-30\",\"recipeYield\":1,\"description\":\"Oatmilk\\u00a0Honey\\u00a0Latte \\u2013 a delicious homemade morning treat! Steaming hot, rich, and dark coffee that\\u2019s satisfyingly creamy, barely sweet, and warm-spiced.\",\"image\":[\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1.jpg\",\"https:\\/\\/demo.wptasty.com\\/wp-content\\/uploads\\/2020\\/11\\/Honey-Oatmilk-Latte-Square-185x185-1-185x180.jpg\"],\"recipeCuisine\":\"american\",\"prepTime\":\"PT5M\",\"cookTime\":\"PT10M\",\"performTime\":\"PT10M\",\"totalTime\":\"PT15M\",\"recipeIngredient\":[\"3\\/4 cup oatmilk\",\"1\\u20132 tablespoons honey (depends how sweet you like it)\",\"1 tiny tiny pinch of salt\",\"1\\/2 cup of strongly brewed coffee (decaf, in my case!)\",\"a dusting of cinnamon, optional\"],\"recipeInstructions\":[{\"@type\":\"HowToStep\",\"text\":\",\\u00a0honey, and salt into a\\u00a0glass jar. Microwave for about a minute, until very hot.\",\"position\":1,\"name\":\"Pour the milk\",\"url\":\"https:\\/\\/demo.wptasty.com\\/2021\\/04\\/06\\/1288\\/#mv_create_13_1\"},{\"@type\":\"HowToStep\",\"text\":\":\\u00a0and wrap with a towel (the metal lid will get hot). Shake until foamy and delicious.\",\"position\":2,\"name\":\"Put a cap on the jar\",\"url\":\"https:\\/\\/demo.wptasty.com\\/2021\\/04\\/06\\/1288\\/#mv_create_13_2\"},{\"@type\":\"HowToStep\",\"text\":\"\\u00a0into a mug. Add coffee. Sprinkle with cinnamon. Your cozy morning awaits.\",\"position\":3,\"name\":\"\\u00a0into a mug. Add coffee. Sprinkle with cinnamon....\",\"url\":\"https:\\/\\/demo.wptasty.com\\/2021\\/04\\/06\\/1288\\/#mv_create_13_3\"}],\"video\":{\"@type\":\"VideoObject\",\"name\":\"Spicy Peanut Soba Noodle Salad\",\"description\":\"This Spicy Peanut Soba Noodle Salad features red peppers, cabbage, chicken, soba noodles, and a quick homemade spicy peanut sauce. Salads don\\u2019t get much yummier than this.\\n\\nFull recipe: https:\\/\\/pinchofyum.com\\/spicy-peanut-soba-noodle-salad\\n\\nInstagram: https:\\/\\/www.instagram.com\\/pinchofyum\\/\\nFacebook: https:\\/\\/www.facebook.com\\/pinchofyum\\/\\nTwitter: https:\\/\\/twitter.com\\/pinchofyum\\nPinterest: https:\\/\\/www.pinterest.com\\/pinchofyum\\/\",\"thumbnailUrl\":\"https:\\/\\/i.ytimg.com\\/vi\\/tjaY2r6XF5w\\/hqdefault.jpg\",\"contentUrl\":\"https:\\/\\/www.youtube.com\\/watch?v=tjaY2r6XF5w\",\"duration\":\"PT1M3S\",\"uploadDate\":\"2021-03-23T14:26:41Z\"},\"keywords\":\"coffee, drink, honey\",\"suitableForDiet\":\"VegetarianDiet\",\"nutrition\":{\"@type\":\"NutritionInformation\",\"calories\":\"320 calories\",\"fatContent\":\"2 grams fat\",\"servingSize\":\"8 oz.\"},\"url\":\"https:\\/\\/demo.wptasty.com\\/2021\\/04\\/06\\/1288\\/\"}', '{\"slug_repaired\":true,\"fixed_associated_posts_column\":true,\"revisions_removed\":true}', '[]', NULL, NULL, 0, 0),
  (20, '2021-05-26 21:22:00', '2021-06-01 19:22:36', 70822, 'recipe', 'Amazing Mushroom Bowls with Kale Pesto', 'Ann Nguyen', '<p>Amazing Mushroom Bowls with Kale Pesto! Smoky, sweet, salt mushrooms (or tempeh!), caramelized pineapple, peppers, and dollop of kale pesto, all served over rice. SO GOOD.</p>', '<ol><li>Whisk or shake the marinate in a jar until combined.</li><li>Place mushrooms in a shallow bowl. Cover with enough marinade to get some flavor on each of the mushroom pieces (you might have some marinade left – that’s okay, you can use it later in this recipe, or save for next week’s bowls).</li><li>Preheat oven to 450 degrees. Let the mushrooms rest for 20-30 minutes while you prep other ingredients, including your sauce! Place peppers, pineapple, and mushrooms with marinade each on their own sheet pans. Bake for 20-or-so minutes, rotating pans as needed, until you get some nice browning on everything (peppers will roast a few minutes faster). You can use the broiler to help with the browning as needed.</li><li>Serve in a bowl over rice or cauli rice. Top with your sauce (yeah kale pesto)! You can also put all this yumminess in a wrap or on a salad. Poof! Look at healthy, delicious you.</li></ol>', '<ol><li>Whisk or shake the marinate in a jar until combined.</li><li>Place mushrooms in a shallow bowl. Cover with enough marinade to get some flavor on each of the mushroom pieces (you might have some marinade left – that’s okay, you can use it later in this recipe, or save for next week’s bowls).</li><li>Preheat oven to 450 degrees. Let the mushrooms rest for 20-30 minutes while you prep other ingredients, including your sauce! Place peppers, pineapple, and mushrooms with marinade each on their own sheet pans. Bake for 20-or-so minutes, rotating pans as needed, until you get some nice browning on everything (peppers will roast a few minutes faster). You can use the broiler to help with the browning as needed.</li><li>Serve in a bowl over rice or cauli rice. Top with your sauce (yeah kale pesto)! You can also put all this yumminess in a wrap or on a salad. Poof! Look at healthy, delicious you.</li></ol>', '<ul><li><strong>Tips for Roasting:</strong> don’t overcrowd the pans because the pineapple, peppers, and mushrooms might start to steam instead of roast / caramelize / brown. I would recommend draining off excess juice from your pineapple if it seems to have a lot of moisture. If your mushrooms look like they have a lot of liquid, that’s okay – keep roasting a bit longer and the sauce will kind of turn into a gooey crust and the mushrooms will get even deeper in color and flavor. YUM.</li></ul>', 1, '', 'VeganDiet', NULL, NULL, 70829, 70829, 140, 30, NULL, 70823, 1200, 1800, 900, 3900, 'Prep Time', 'Cook Time', 'Custom Time', 'prep_time,active_time,additional_time', '4', NULL, NULL, NULL, NULL, '{\"id\":\"A73lRQop_zA\",\"name\":\"Amazing Mushrooms Bowls with Kale Pesto\",\"description\":\"Amazing Mushroom Bowls with Kale Pesto! Smoky, sweet, salt mushrooms (or tempeh!), caramelized pineapple, peppers, and dollop of kale pesto, all served over rice. SO GOOD.\\n\\nFull recipe: https://pinchofyum.com/mushroom-bowls-with-kale-pesto\\n\\nInstagram: https://www.instagram.com/pinchofyum/\\nFacebook: https://www.facebook.com/pinchofyum/\\nTwitter: https://twitter.com/pinchofyum\\nPinterest: https://www.pinterest.com/pinchofyum/\",\"duration\":\"PT1M\",\"thumbnailUrl\":\"https://i.ytimg.com/vi/A73lRQop_zA/hqdefault.jpg\",\"uploadDate\":\"2020-03-19T18:17:54Z\",\"contentUrl\":\"https://www.youtube.com/watch?v=A73lRQop_zA\",\"display\":true,\"source\":\"YOUTUBE\"}', NULL, '[\"70829\"]', '4.6', '64', '{\"@context\":\"http:\\/\\/schema.org\",\"@type\":\"Recipe\",\"name\":\"Amazing Mushroom Bowls with Kale Pesto\",\"author\":{\"@type\":\"Person\",\"name\":\"Ann Nguyen\"},\"datePublished\":\"2021-05-26\",\"recipeYield\":4,\"description\":\"Amazing Mushroom Bowls with Kale Pesto! Smoky, sweet, salt mushrooms (or tempeh!), caramelized pineapple, peppers, and dollop of kale pesto, all served over rice. SO GOOD.\",\"image\":[\"http:\\/\\/tastyrecipes.test\\/wp-content\\/uploads\\/2021\\/05\\/Mushroom-Bowls-1-720x720.jpeg\",\"http:\\/\\/tastyrecipes.test\\/wp-content\\/uploads\\/2021\\/05\\/Mushroom-Bowls-1-720x540.jpeg\",\"http:\\/\\/tastyrecipes.test\\/wp-content\\/uploads\\/2021\\/05\\/Mushroom-Bowls-1-720x405.jpeg\",\"http:\\/\\/tastyrecipes.test\\/wp-content\\/uploads\\/2021\\/05\\/Mushroom-Bowls-1-735x1103.jpeg\"],\"recipeCategory\":\"Bowls\",\"recipeCuisine\":\"american\",\"prepTime\":\"PT20M\",\"cookTime\":\"PT30M\",\"performTime\":\"PT45M\",\"totalTime\":\"PT1H5M\",\"recipeIngredient\":[\"1\\/4 cup olive oil\",\"1\\/4 cup maple syrup\",\"1\\/4 cup soy sauce\",\"1 teaspoon smoked paprika\",\"1\\/2 teaspoon cumin\",\"1\\/2 teaspoon Italian seasoning\",\"1\\/2 teaspoon onion powder\",\"1\\/2 teaspoon garlic powder\",\"salt and black pepper (to taste)\",\"16 ounces\\u00a0fresh mushrooms, washed and cut into thick-ish slices\",\"2 cups\\u00a0fresh pineapple chunks\",\"2\\u20133\\u00a0bell peppers\",\"rice\\u00a0or\\u00a0cauliflower rice\",\"kale pesto,\\u00a0magic green sauce, or any other\\u00a0yummy sauce\"],\"recipeInstructions\":[{\"@type\":\"HowToStep\",\"text\":\"Whisk or shake the marinate in a jar until combined.\",\"position\":1,\"name\":\"Whisk or shake the marinate in a jar...\",\"url\":\"http:\\/\\/tastyrecipes.test\\/?p=70829#mv_create_20_1\"},{\"@type\":\"HowToStep\",\"text\":\"Place mushrooms in a shallow bowl. Cover with enough marinade to get some flavor on each of the mushroom pieces (you might have some marinade left \\u2013 that\\u2019s okay, you can use it later in this recipe, or save for next week\\u2019s bowls).\",\"position\":2,\"name\":\"Place mushrooms in a shallow bowl. Cover with...\",\"url\":\"http:\\/\\/tastyrecipes.test\\/?p=70829#mv_create_20_2\"},{\"@type\":\"HowToStep\",\"text\":\"Preheat oven to 450 degrees. Let the mushrooms rest for 20-30 minutes while you prep other ingredients, including your sauce! Place peppers, pineapple, and mushrooms with marinade each on their own sheet pans. Bake for 20-or-so minutes, rotating pans as needed, until you get some nice browning on everything (peppers will roast a few minutes faster). You can use the broiler to help with the browning as needed.\",\"position\":3,\"name\":\"Preheat oven to 450 degrees. Let the mushrooms...\",\"url\":\"http:\\/\\/tastyrecipes.test\\/?p=70829#mv_create_20_3\"},{\"@type\":\"HowToStep\",\"text\":\"Serve in a bowl over rice or cauli rice. Top with your sauce (yeah kale pesto)! You can also put all this yumminess in a wrap or on a salad. Poof! Look at healthy, delicious you.\",\"position\":4,\"name\":\"Serve in a bowl over rice or cauli...\",\"url\":\"http:\\/\\/tastyrecipes.test\\/?p=70829#mv_create_20_4\"}],\"video\":{\"@type\":\"VideoObject\",\"name\":\"Amazing Mushrooms Bowls with Kale Pesto\",\"description\":\"Amazing Mushroom Bowls with Kale Pesto! Smoky, sweet, salt mushrooms (or tempeh!), caramelized pineapple, peppers, and dollop of kale pesto, all served over rice. SO GOOD.\\n\\nFull recipe: https:\\/\\/pinchofyum.com\\/mushroom-bowls-with-kale-pesto\\n\\nInstagram: https:\\/\\/www.instagram.com\\/pinchofyum\\/\\nFacebook: https:\\/\\/www.facebook.com\\/pinchofyum\\/\\nTwitter: https:\\/\\/twitter.com\\/pinchofyum\\nPinterest: https:\\/\\/www.pinterest.com\\/pinchofyum\\/\",\"thumbnailUrl\":\"https:\\/\\/i.ytimg.com\\/vi\\/A73lRQop_zA\\/hqdefault.jpg\",\"contentUrl\":\"https:\\/\\/www.youtube.com\\/watch?v=A73lRQop_zA\",\"duration\":\"PT1M\",\"uploadDate\":\"2020-03-19T18:17:54Z\"},\"suitableForDiet\":\"VeganDiet\",\"nutrition\":{\"@type\":\"NutritionInformation\",\"calories\":\"450 calories\",\"carbohydrateContent\":\"20 grams carbohydrates\",\"cholesterolContent\":\"0 milligrams cholesterol\",\"fatContent\":\"12 grams fat\",\"fiberContent\":\"5 grams fiber\",\"proteinContent\":\"18 grams protein\",\"saturatedFatContent\":\"2 grams saturated fat\",\"servingSize\":\"2 cups\",\"sodiumContent\":\"1400 grams sodium\",\"sugarContent\":\"2 grams sugar\",\"transFatContent\":\"0 grams trans fat\",\"unsaturatedFatContent\":\"10 grams unsaturated fat\"},\"url\":\"http:\\/\\/tastyrecipes.test\\/?p=70829\"}', '{\"slug_repaired\":true,\"fixed_associated_posts_column\":true,\"revisions_removed\":true}', '[]', NULL, NULL, 0, 0);


/*!40000 ALTER TABLE `wptests_mv_creations` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `wptests_mv_nutrition`;
CREATE TABLE `wptests_mv_nutrition` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `creation` bigint DEFAULT NULL,
  `serving_size` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `number_of_servings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `calories` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `total_fat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `saturated_fat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `trans_fat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `unsaturated_fat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `cholesterol` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `sodium` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `carbohydrates` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `fiber` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `sugar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `protein` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `net_carbs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `sugar_alcohols` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `calculated` datetime DEFAULT NULL,
  `source` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `display_zeros` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`),
  KEY `creation` (`creation`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40000 ALTER TABLE `wptests_mv_nutrition` DISABLE KEYS */;

INSERT INTO `wptests_mv_nutrition` (`id`, `created`, `modified`, `creation`, `serving_size`, `number_of_servings`, `calories`, `total_fat`, `saturated_fat`, `trans_fat`, `unsaturated_fat`, `cholesterol`, `sodium`, `carbohydrates`, `fiber`, `sugar`, `protein`, `net_carbs`, `sugar_alcohols`, `calculated`, `source`, `display_zeros`)
VALUES
  (12, '2021-05-26 21:25:50', '2021-05-26 21:26:23', 20, '2 cups', '4', '450', '12', '2', '0', '10', '0', '1400', '20', '5', '2', '18', '', '', '0000-00-00 00:00:00', '', '');


/*!40000 ALTER TABLE `wptests_mv_nutrition` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `wptests_mv_supplies`;
CREATE TABLE `wptests_mv_supplies` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `creation` bigint DEFAULT NULL,
  `original_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `note` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `link` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `group` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `position` mediumint DEFAULT NULL,
  `amount` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `max_amount` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `nofollow` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `creation` (`creation`)
) ENGINE=InnoDB AUTO_INCREMENT=917 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40000 ALTER TABLE `wptests_mv_supplies` DISABLE KEYS */;
INSERT INTO `wptests_mv_supplies` (`id`, `created`, `modified`, `type`, `creation`, `original_text`, `note`, `link`, `group`, `position`, `amount`, `max_amount`, `nofollow`)
VALUES
  (171, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '2 Cups Sugar', NULL, NULL, 'mv-has-no-group', 0, '2', NULL, 1),
  (172, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '1/3 Cup Unsweetened Cocoa Powder', NULL, NULL, 'mv-has-no-group', 1, '0.333', NULL, 1),
  (173, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '8 Tbsp Butter', NULL, NULL, 'mv-has-no-group', 2, '8', NULL, 1),
  (174, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '1/2 Cup Milk', NULL, NULL, 'mv-has-no-group', 3, '0.5', NULL, 1),
  (175, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '1/2 Cup Peanut Butter', NULL, NULL, 'mv-has-no-group', 4, '0.5', NULL, 1),
  (176, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '3 Cups Quick-Cook Oatmeal', NULL, NULL, 'mv-has-no-group', 5, '3', NULL, 1),
  (177, '2020-07-20 20:35:37', '2020-07-20 20:35:37', 'ingredients', 10, '2 Tbsp Vanilla Extract', NULL, NULL, 'mv-has-no-group', 6, '2', NULL, 1),
  (238, '2021-03-30 22:32:10', '2021-03-30 22:32:10', 'ingredients', 13, '3/4 cup oatmilk', NULL, NULL, 'mv-has-no-group', 0, '4', NULL, 1),
  (239, '2021-03-30 22:32:10', '2021-03-30 22:32:10', 'ingredients', 13, '1–2 tablespoons honey (depends how sweet you like it)', 'depends how sweet you like it', NULL, 'mv-has-no-group', 1, '1', '2', 1),
  (240, '2021-03-30 22:32:10', '2021-03-30 22:32:10', 'ingredients', 13, '1 tiny tiny pinch of salt', NULL, NULL, 'mv-has-no-group', 2, '1', NULL, 1),
  (241, '2021-03-30 22:32:10', '2021-03-30 22:32:10', 'ingredients', 13, '1/2 cup of strongly brewed coffee (decaf, in my case!)', 'in my case!)', NULL, 'mv-has-no-group', 3, '0.5', NULL, 1),
  (242, '2021-03-30 22:32:10', '2021-03-30 22:32:10', 'ingredients', 13, 'a dusting of cinnamon, optional', 'optional', NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (313, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '1 chipotle pepper (or half of one pepper, for a less spicy version)', 'for a less spicy version)', NULL, 'mv-has-no-group', 0, '1', NULL, 1),
  (314, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '3/4 cup heavy cream', NULL, NULL, 'mv-has-no-group', 1, '4', NULL, 1),
  (315, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '2 cloves garlic', NULL, NULL, 'mv-has-no-group', 2, '2', NULL, 1),
  (316, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '1/2 teaspoon salt', NULL, NULL, 'mv-has-no-group', 3, '0.5', NULL, 1),
  (317, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, 'zest of one orange', NULL, NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (318, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, 'a drizzle of olive oil', NULL, NULL, 'mv-has-no-group', 5, NULL, NULL, 1),
  (319, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '1 pound uncooked shrimp, tails removed, patted dry', 'tails removed, patted dry', NULL, 'mv-has-no-group', 6, '1', NULL, 1),
  (320, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '1/2 teaspoon cumin', NULL, NULL, 'mv-has-no-group', 7, '0.5', NULL, 1),
  (321, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, '1 1/2 cups white rice (I like jasmine rice or just plain long grain rice)', 'I like jasmine rice or just plain long grain rice', NULL, 'mv-has-no-group', 8, '1.5', NULL, 1),
  (322, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, 'a big handful of chopped cilantro', NULL, NULL, 'mv-has-no-group', 9, NULL, NULL, 1),
  (323, '2021-05-25 21:15:03', '2021-05-25 21:15:03', 'ingredients', 14, 'optional, for serving: salad, slaw, zippy cucumbers (see notes)', 'slaw, zippy cucumbers (see notes)', NULL, 'mv-has-no-group', 10, NULL, NULL, 1),
  (326, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, '2 russet potatoes', NULL, NULL, 'mv-has-no-group', 0, '2', NULL, 1),
  (327, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, '227 gram fresh mushrooms, sliced', 'sliced', NULL, 'mv-has-no-group', 1, '227', NULL, 1),
  (328, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, '1 lb. chicken thighs or chicken breasts', NULL, NULL, 'mv-has-no-group', 2, '1', NULL, 1),
  (329, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, '63 to 125 mL barbecue sauce', NULL, NULL, 'mv-has-no-group', 3, '63', NULL, 1),
  (330, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, 'a sprinkle of garlic power, onion powder, salt, and pepper', 'onion powder, salt, and pepper', NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (331, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, '68 gram garlic herb spreadable cheese, or garlic herb goat cheese', 'or garlic herb goat cheese', NULL, 'mv-has-no-group', 5, '68', NULL, 1),
  (332, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, 'a handful of fresh spinach', NULL, NULL, 'mv-has-no-group', 6, NULL, NULL, 1),
  (333, '2021-05-25 21:33:30', '2021-05-25 21:33:30', 'ingredients', 15, 'optional toppings: chives, crunchy onions, bleu cheese crumbles', 'crunchy onions, bleu cheese crumbles', NULL, 'mv-has-no-group', 7, NULL, NULL, 1),
  (334, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, '8 ounces DeLallo spirali pasta (okay, you don’t have to use spirali, but it is SO FUN)', 'you don’t have to use spirali, but it is SO FUN)', NULL, 'mv-has-no-group', 0, '8', NULL, 1),
  (335, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, '2 cloves garlic, minced', 'minced', NULL, 'mv-has-no-group', 1, '2', NULL, 1),
  (336, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, '1/4 cup DeLallo sun-dried tomatoes packed in oil, sliced or chopped', 'sliced or chopped', NULL, 'mv-has-no-group', 2, '0.25', NULL, 1),
  (337, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, 'about 1/3 cup dry white wine', NULL, NULL, 'mv-has-no-group', 3, '0.333', NULL, 1),
  (338, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, '2/3 – 3/4 cup heavy cream', NULL, NULL, 'mv-has-no-group', 4, '0.666', NULL, 1),
  (339, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, 'up to 1/2 cup reserved pasta water', NULL, NULL, 'mv-has-no-group', 5, NULL, NULL, 1),
  (340, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, 'salt and pepper to taste', NULL, NULL, 'mv-has-no-group', 6, NULL, NULL, 1),
  (341, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, 'a big bunch of chopped chives or parsley', NULL, NULL, 'mv-has-no-group', 7, NULL, NULL, 1),
  (342, '2021-05-25 21:36:43', '2021-05-25 21:36:43', 'ingredients', 16, 'parmesan for serving', NULL, NULL, 'mv-has-no-group', 8, NULL, NULL, 1),
  (581, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '2 cups uncooked quinoa', NULL, NULL, 'mv-has-no-group', 0, '2', NULL, 1),
  (582, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1 bunch kale', NULL, NULL, 'mv-has-no-group', 1, '1', NULL, 1),
  (583, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1 avocado', NULL, NULL, 'mv-has-no-group', 2, '1', NULL, 1),
  (584, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1 lb. boneless skinless chicken breasts', NULL, NULL, 'mv-has-no-group', 3, '1', NULL, 1),
  (585, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, 'sesame seeds or crushed red pepper flakes for topping', NULL, NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (586, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1 16 ounce jar sun dried tomatoes, drained', 'drained', NULL, 'mv-has-no-group', 5, '1', NULL, 1),
  (587, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1–2 cloves garlic', NULL, NULL, 'mv-has-no-group', 6, '1', '2', 1),
  (588, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '½ teaspoon salt (more to taste – I prefer more like 1 teaspoon)', 'more to taste – I prefer more like 1 teaspoon', NULL, 'mv-has-no-group', 7, '0.5', NULL, 1),
  (589, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, 'juice of one lemon', NULL, NULL, 'mv-has-no-group', 8, NULL, NULL, 1),
  (590, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '½ cup almonds', NULL, NULL, 'mv-has-no-group', 9, '0.5', NULL, 1),
  (591, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '½ cup olive oil', NULL, NULL, 'mv-has-no-group', 10, '0.5', NULL, 1),
  (592, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1/3 cup olive oil', NULL, '', 'Marinade', 11, '', NULL, 1),
  (593, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, 'a few good squeezes of lemon juice (totaling about 3 tablespoons)', NULL, '', 'Marinade', 12, '', NULL, 1),
  (594, '2021-05-25 21:42:34', '2021-05-25 21:42:34', 'ingredients', 17, '1/2 teaspoon salt', NULL, '', 'Marinade', 13, '', NULL, 1),
  (595, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '5 pounds of potatoes, peeled and halved (see notes)', 'peeled and halved (see notes)', NULL, 'mv-has-no-group', 0, '5', NULL, 1),
  (596, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '4 teaspoons salt, divided', 'divided', NULL, 'mv-has-no-group', 1, '4', NULL, 1),
  (597, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '2 sticks butter (1 cup)', '1 cup', NULL, 'mv-has-no-group', 2, '2', NULL, 1),
  (598, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '2–4 cloves garlic', NULL, NULL, 'mv-has-no-group', 3, '2', '4', 1),
  (599, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, 'a few sprigs of herbs (sage, thyme, etc.)', 'thyme, etc.)', NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (600, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '1 cup cream cheese or sour cream', NULL, NULL, 'mv-has-no-group', 5, '1', NULL, 1),
  (601, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '1/4 cup grated Parmesan cheese (optional)', 'optional', NULL, 'mv-has-no-group', 6, '0.25', NULL, 1),
  (602, '2021-05-25 22:19:57', '2021-05-25 22:19:57', 'ingredients', 18, '1/2 cup broth or milk (optional, for texture)', 'for texture)', NULL, 'mv-has-no-group', 7, '0.5', NULL, 1),
  (715, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '8 ounces pasta (half of a box of farfalle, rigatoni, penne, whatever you like)', 'rigatoni, penne, whatever you like)', NULL, 'mv-has-no-group', 0, '8', NULL, 1),
  (716, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '6 cups fresh, seasonal veggies (see notes)', 'seasonal veggies (see notes)', NULL, 'mv-has-no-group', 1, '6', NULL, 1),
  (717, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '3 cloves garlic', NULL, NULL, 'mv-has-no-group', 2, '3', NULL, 1),
  (718, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, 'a drizzle of olive oil', NULL, NULL, 'mv-has-no-group', 3, NULL, NULL, 1),
  (719, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, 'salt to taste', NULL, NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (720, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '1 cup walnuts', NULL, NULL, 'mv-has-no-group', 5, '1', NULL, 1),
  (721, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '1 1/2 cups tightly packed basil leaves (you can sub other greens if you don’t have enough basil)', 'you can sub other greens if you don’t have enough basil', NULL, 'mv-has-no-group', 6, '1.5', NULL, 1),
  (722, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '1/4 cup olive oil', NULL, NULL, 'mv-has-no-group', 7, '0.25', NULL, 1),
  (723, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '1 very small clove of garlic', NULL, NULL, 'mv-has-no-group', 8, '1', NULL, 1),
  (724, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, 'juice of 1 lemon (just use half if it’s really juicy)', 'just use half if it’s really juicy', NULL, 'mv-has-no-group', 9, NULL, NULL, 1),
  (725, '2021-05-26 21:18:25', '2021-05-26 21:18:25', 'ingredients', 19, '1/2 teaspoon kosher salt', NULL, NULL, 'mv-has-no-group', 10, '0.5', NULL, 0),
  (897, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/4 cup olive oil', NULL, NULL, 'mv-has-no-group', 0, '0.25', NULL, 1),
  (898, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/4 cup maple syrup', NULL, NULL, 'mv-has-no-group', 1, '0.25', NULL, 1),
  (899, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/4 cup soy sauce', NULL, NULL, 'mv-has-no-group', 2, '0.25', NULL, 1),
  (900, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1 teaspoon smoked paprika', NULL, NULL, 'mv-has-no-group', 3, '1', NULL, 1),
  (901, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/2 teaspoon cumin', NULL, NULL, 'mv-has-no-group', 4, '0.5', NULL, 1),
  (902, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/2 teaspoon Italian seasoning', NULL, NULL, 'mv-has-no-group', 5, '0.5', NULL, 1),
  (903, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/2 teaspoon onion powder', NULL, NULL, 'mv-has-no-group', 6, '0.5', NULL, 1),
  (904, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '1/2 teaspoon garlic powder', NULL, NULL, 'mv-has-no-group', 7, '0.5', NULL, 1),
  (905, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, 'salt and black pepper (to taste)', 'to taste', NULL, 'mv-has-no-group', 8, NULL, NULL, 1),
  (906, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '16 ounces fresh mushrooms, washed and cut into thick-ish slices', NULL, '', 'Bowls', 9, '', NULL, 1),
  (907, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '2 cups fresh pineapple chunks', NULL, '', 'Bowls', 10, '', NULL, 1),
  (908, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, '2–3 bell peppers', NULL, '', 'Bowls', 11, '', NULL, 1),
  (909, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, 'rice or cauliflower rice', NULL, '', 'Bowls', 12, '', NULL, 1),
  (910, '2021-05-26 21:24:51', '2021-05-26 21:24:51', 'ingredients', 20, 'kale pesto, magic green sauce, or any other yummy sauce', NULL, 'https://pinchofyum.com/5-minute-vegan-kale-pesto', 'Bowls', 13, '', NULL, 1),
  (911, '2021-05-26 21:28:10', '2021-05-26 21:28:10', 'ingredients', 21, '2 cups torn kale (no stems) and parsley, combined', 'combined', NULL, 'mv-has-no-group', 0, '2', NULL, 1),
  (912, '2021-05-26 21:28:10', '2021-05-26 21:28:10', 'ingredients', 21, '1/2 cup olive oil', NULL, NULL, 'mv-has-no-group', 1, '0.5', NULL, 1),
  (913, '2021-05-26 21:28:10', '2021-05-26 21:28:10', 'ingredients', 21, '1/4 teaspoon salt (more to taste)', 'more to taste', NULL, 'mv-has-no-group', 2, '0.25', NULL, 1),
  (914, '2021-05-26 21:28:10', '2021-05-26 21:28:10', 'ingredients', 21, '1 clove garlic', NULL, NULL, 'mv-has-no-group', 3, '1', NULL, 1),
  (915, '2021-05-26 21:28:10', '2021-05-26 21:28:10', 'ingredients', 21, 'juice of one lemon', NULL, NULL, 'mv-has-no-group', 4, NULL, NULL, 1),
  (916, '2021-05-26 21:28:10', '2021-05-26 21:28:10', 'ingredients', 21, '1/4 to 1/2 cup raw almonds', NULL, NULL, 'mv-has-no-group', 5, '0.25', NULL, 1);

/*!40000 ALTER TABLE `wptests_mv_supplies` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `wptests_term_relationships`;
CREATE TABLE `wptests_term_relationships` (
  `object_id` bigint unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint unsigned NOT NULL DEFAULT '0',
  `term_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40000 ALTER TABLE `wptests_term_relationships` DISABLE KEYS */;
INSERT INTO `wptests_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`)
VALUES
  (1270, 30, 0),
  (70822, 30, 0),
  (70822, 140, 0);

/*!40000 ALTER TABLE `wptests_term_relationships` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `wptests_term_taxonomy`;
CREATE TABLE `wptests_term_taxonomy` (
  `term_taxonomy_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint unsigned NOT NULL DEFAULT '0',
  `count` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40000 ALTER TABLE `wptests_term_taxonomy` DISABLE KEYS */;

INSERT INTO `wptests_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`)
VALUES
  (30, 30, 'mv_cuisine', '', 0, 7),
  (140, 140, 'category', '', 0, 0),
  (146, 146, 'mv_cuisine', '', 0, 3);


/*!40000 ALTER TABLE `wptests_term_taxonomy` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `wptests_terms`;
CREATE TABLE `wptests_terms` (
  `term_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40000 ALTER TABLE `wptests_terms` DISABLE KEYS */;
INSERT INTO `wptests_terms` (`term_id`, `name`, `slug`, `term_group`)
VALUES
  (30, 'american', 'american', 0),
  (140, 'Bowls', 'bowl', 0),
  (146, 'italian', 'italian', 0);

  
/*!40000 ALTER TABLE `wptests_terms` ENABLE KEYS */;
UNLOCK TABLES;


DROP TABLE IF EXISTS `wptests_posts`;
CREATE TABLE `wptests_posts` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int NOT NULL DEFAULT '0',
  `post_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB AUTO_INCREMENT=70831 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

/*!40000 ALTER TABLE `wptests_posts` DISABLE KEYS */;
INSERT INTO `wptests_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`)
VALUES
  (1270, 2, '2021-03-30 22:31:25', '2021-03-30 22:31:25', '', 'Oatmilk Honey Latte Creation', '', 'publish', 'closed', 'closed', '', 'oatmilk-honey-latte-creation', '', '', '2021-03-30 22:31:25', '2021-03-30 22:31:25', '', 0, 'https://demo.wptasty.com/?post_type=mv_create&p=1270', 0, 'mv_create', '', 0),
  (70822, 2, '2021-05-26 21:22:00', '2021-05-26 21:22:00', '', 'Amazing Mushroom Bowls with Kale Pesto Creation', '', 'publish', 'closed', 'closed', '', 'amazing-mushroom-bowls-with-kale-pesto-creation', '', '', '2021-05-26 21:22:00', '2021-05-26 21:22:00', '', 0, 'https://demo.wptasty.com/?post_type=mv_create&p=70822', 0, 'mv_create', '', 0);

  
/*!40000 ALTER TABLE `wptests_posts` ENABLE KEYS */;
UNLOCK TABLES;
