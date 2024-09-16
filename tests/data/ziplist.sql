DROP TABLE IF EXISTS `wptests_amd_zlrecipe_recipes`;
CREATE TABLE `wptests_amd_zlrecipe_recipes` (
  `recipe_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `recipe_title` text DEFAULT NULL,
  `recipe_image` text DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `rating` text DEFAULT NULL,
  `prep_time` text DEFAULT NULL,
  `cook_time` text DEFAULT NULL,
  `total_time` text DEFAULT NULL,
  `yield` text DEFAULT NULL,
  `serving_size` varchar(50) DEFAULT NULL,
  `calories` varchar(50) DEFAULT NULL,
  `fat` varchar(50) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `carbs` varchar(50) DEFAULT NULL,
  `protein` varchar(50) DEFAULT NULL,
  `fiber` varchar(50) DEFAULT NULL,
  `sugar` varchar(50) DEFAULT NULL,
  `saturated_fat` varchar(50) DEFAULT NULL,
  `sodium` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `cuisine` varchar(50) DEFAULT NULL,
  `trans_fat` varchar(50) DEFAULT NULL,
  `cholesterol` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`recipe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
LOCK TABLES `wptests_amd_zlrecipe_recipes` WRITE;
INSERT INTO `wptests_amd_zlrecipe_recipes` (`recipe_id`, `post_id`, `recipe_title`, `recipe_image`, `summary`, `rating`, `prep_time`, `cook_time`, `total_time`, `yield`, `serving_size`, `calories`, `fat`, `ingredients`, `instructions`, `notes`, `created_at`, `carbs`, `protein`, `fiber`, `sugar`, `saturated_fat`, `sodium`, `category`, `cuisine`, `trans_fat`, `cholesterol`)
VALUES
  (1,33484,'Autumn Apple Ro\'se Sangria','http://pinchofyum.com/wp-content/uploads/Miracle-No-Knead-Bread-3-2-Square.jpg','This festive _Autumn Apple Rosé Sangria_ is bubbly, just the right amount of sweet, and perfect for your Thanksgiving or other fall celebrations!','0','PT10M',NULL,NULL,'8 servings','1 cup','300','0g','1 bottle rosé wine\r\n2 cups apple cider\r\n1 cup brandy\r\n1 cup club soda\r\n3 apples, cored, quartered, and thickly sliced\r\n1 lime, quartered\r\n1 lemon, quartered\r\n3 cinnamon sticks\r\n1 orange, quartered and thickly sliced','Combine all ingredients in a pitcher. Chill for at least 4 hours, but preferably overnight, before serving.','','2016-12-21 16:09:06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
  (2,33487,'Vegan Gingerbread Cookies','','','0','PT1H20M','PT8M',NULL,'Lots of cookies','1 cookie','150','6g','!For the cookies:\r\n\r\n3 1/4 cups of all-purpose flour\r\n1/2 cup of packed dark-brown sugar\r\n2 1/2 tablespoons of homemade gingerbread spice**\r\n1/2 teaspoon salt\r\n3/4 teaspoon of baking soda\r\n3/4 cup of vegan butter, softened*\r\n1 flax egg (1 tablespoon of ground flax seen mixed with 3 tablespoons of water)\r\n1/2 cup unsulfured molasses\r\n\r\n!For the icing which is optional but yummy!:\r\n\r\n1 cup powdered sugar\r\n1-2 Tablespoons almond milk\r\n1/8 teaspoon vanilla extract','In a large bowl, whisk together the flour, baking soda, salt and gingerbread spice*. Set aside.\r\nIn the bowl of a stand mixer using the paddle attachment, beat together the butter and brown sugar until nice and fluffy, about 2 minutes.\r\nAdd in the flax egg and molasses and mix until just combined.\r\nWhile mixing on low speed, gradually add in the flour mixture until it\'s all added and the dough is fully mixed.\r\nDivide the dough into thirds and wrap each dough ball in plastic wrap. Place in the fridge to chill for at least an hour but could even chill it overnight.\r\nWhen ready to roll the dough out, let it sit out on the counter for at least 5 minutes.\r\nBefore beginning to roll out, pre-heat oven to 350 degrees F.\r\nOn a generously floured surface and with floured hands and rolling pin, roll out one of your thirds of dough. You can make this a bit easier by rolling it out on parchment paper. You want the dough to be about 1/8th of an inch thick before cutting out the shapes.\r\nIf you had to work the dough a lot with your hands before rolling it out and cutting the shapes, you might want to put the rolled out dough back in the fridge for a few minutes before cutting your shapes out.\r\nCut shapes out and place them on a lined baking sheet. You can place these relatively close together since they don\'t spread much at all.\r\nBake for 7-9 minutes. Let cool completely.\r\nWhen ready to decorate, whisk together the frosting ingredients in a small bowl and then transfer the frosting to a small baggie. Cutting a small hole in the corner of the bag and pipe your frosting onto the cookies.\r\nENJOY!!','**I typically use Earth Balance butter sticks when making cookies but their butter out of the tub works just the same.\r\n**If you don\'t want to make a whole batch of the spice just for this recipe, you can substitute the 2 1/2 tablespoons of gingerbread spice with 1 tablespoon of cinnamon, 1 tablespoon of ginger, 1/2 teaspoon of clove, 1/2 teaspoon of all spice, 1/2 teaspoon of nutmeg.','2016-12-21 08:13:02',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
  (3,33490,'Pomegranate Chocolate Chip Pancakes','','Pomegranate and chocolate make a perfect pairing in these pomegranate chocolate chip pancakes! These pancakes are vegan but you\'d never know it - give them a try!','0','PT10M','PT10M','PT20M','8-12 pancakes','1 pancake','250','6g','!For the pancakes\r\n2 cups whole wheat pastry flour\r\n3 T. organic sugar, coconut sugar or other sugar of choice\r\n2 T. baking powder\r\n¾ tsp. salt\r\n3 T. ground flax seed\r\n2 cups non-dairy milk\r\n¼ cup aquafaba (liquid from canned chickpeas or white beans), or water\r\n1 t. vanilla extract\r\n1 cup pomegranate arils (from 1 pomegranate)\r\n½ cup chocolate chips\r\n!For the pomegranate syrup\r\n1 cup pomegranate seeds (from 1 pomegranate)\r\n¾ cup apple cider\r\n¾ cup maple syrup\r\n<a href=\"http://eatwithinyourmeans.com/pomegranate-chocolate-chip-pancakes/\" target=\"_blank\">This is a link to test</a>','!Make the Pancakes\r\nPreheat an electric skillet to *375F* or heat a cast iron or nonstick skillet on medium heat.\r\nIn a medium bowl, _stir together_ the dry ingredients. Stir in the chocolate chips to coat with the flour. Set the pomegranate arils to the side.\r\nIn a 4-cup measuring cup or small bowl, measure and mix the liquid ingredients together.\r\nAdd the liquid ingredients to the dry and mix until just combined (small lumps are ok).\r\nUsing a large ice cream disher or ¼ cup measure, pour batter onto skillet and cook for 2 minutes. While the pancakes are cooking, sprinkle pomegranate arils on top of the batter.\r\nAfter 2 minutes are up, or the bottom of the pancakes look golden brown, flip the pancakes carefully and cook for 2 minutes more.\r\n!Make the Pomegranate Syrup\r\nPress out the juice from the pomegranate arils through a fine meshed sieve into a small saucepan. Add the maple syrup and apple cider to the saucepan, and bring to a boil.\r\nReduce the temperature to medium and let the mixture reduce by approximately half, or until the syrup coats the back of a spoon.\r\nServe the pancakes with the pomegranate syrup (if using), or with maple syrup.','','2016-12-21 08:17:53',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
  (6,33854,'Zip Recipes 1','http://tasty.handbuilt.co/wp-content/uploads/2017/11/Korean-BBQ-Beef-Tacos.jpg','This is some description text',NULL,'PT4H5M','PT10M','PT5H30M','3','1/2 cup','300','20g','','','','2018-03-14 14:23:41','30','20','4','16','0g','230mg','Entree','French','1g','50');
UNLOCK TABLES;

DROP TABLE IF EXISTS `wptests_comments`;
CREATE TABLE `wptests_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT 0,
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB AUTO_INCREMENT=1744 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LOCK TABLES `wptests_comments` WRITE;
INSERT INTO `wptests_comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`, `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`, `comment_parent`, `user_id`)
VALUES
  (1742, 33484, 'bjork', 'bjork.ostrom@gmail.com', '', '66.215.48.178', '2018-10-01 21:16:51', '2018-10-01 21:16:51', '3 stars', 0, '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36', '', 0, 2),
  (1743, 33484, 'Raquel', 'hi@hi.com', '', '66.215.48.178', '2018-10-01 21:17:16', '2018-10-01 21:17:16', 'Four stars', 0, '1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36', '', 0, 0);
UNLOCK TABLES;

DROP TABLE IF EXISTS `wptests_commentmeta`;
CREATE TABLE `wptests_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB AUTO_INCREMENT=4022 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
LOCK TABLES `wptests_commentmeta` WRITE;
INSERT INTO `wptests_commentmeta` (`meta_id`, `comment_id`, `meta_key`, `meta_value`)
VALUES
  (4017, 1742, 'zrdn_post_recipe_rating', '3'),
  (4020, 1743, 'zrdn_post_recipe_rating', '4');
UNLOCK TABLES;


DROP TABLE IF EXISTS `wptests_zrdn_visitor_ratings`;
CREATE TABLE `wptests_zrdn_visitor_ratings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `recipe_id` bigint(20) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `IP` char(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modified_date` datetime NOT NULL,
  `created_date` datetime NOT NULL,
  `time_limit` bigint(20) unsigned NOT NULL,
  `user_id` char(37) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `wptests_zrdn_visitor_ratings` WRITE;

INSERT INTO `wptests_zrdn_visitor_ratings` (`id`, `recipe_id`, `rating`, `IP`, `modified_date`, `created_date`, `time_limit`, `user_id`)
VALUES
  (1,3,5,'66.215.48.178','2018-10-01 21:14:04','2018-10-01 21:14:04',5,'2c70dab3-e60c-b9a3-e82e-d19709cce430'),
  (2,3,3,'66.215.48.178','2018-10-01 21:14:10','2018-10-01 21:14:10',5,'f506e38a-a189-beef-7091-eaa485d88da7');

UNLOCK TABLES;

