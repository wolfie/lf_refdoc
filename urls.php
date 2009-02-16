<?php
addURL('^media/(?P<file>[^/]+)/$', 'basic/passfile', array('dir'=> 'media'));
addURL('^$', 'manual/index');
addURL('^(?P<section>[^/]+)/$', 'manual/section');
addURL('^(?P<section>[^/]+)/(?P<page>[^/]+)/$', 'manual/page');
