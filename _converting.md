# Converting an existing Jul install

I would not advise converting an existing install of Jul, because numerous things have been stripped out (for being too community-specific). Including all emoji. You might find that your forum looks quite different afterwards.

Some database fields were removed as well. To alter the database to be compatible with Jul-Dada:

```
alter table `users` drop `layout`;
drop table `tlayouts`;
alter table `ipbans` drop `date`;
alter table `ipbans` add `datetime_utc` datetime null default null after `perm`;
alter table `users` add `banned` tinyint not null default '0' after `pronouns`;
alter table `users` add `banreason` varchar(255) not null after `banned`;
alter table `users` ADD `banperm` TINYINT NOT NULL DEFAULT '0' AFTER `banreason`;
ALTER TABLE `users` ADD `bandatetime_utc` DATETIME NULL DEFAULT NULL AFTER `banperm`;
-- add the settings table
```

