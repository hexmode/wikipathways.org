<?php
/** Ingush (ГІалгІай Ğalğaj)
 *
 * See MessagesQqq.php for message documentation incl. usage of parameters
 * To improve a translation please visit http://translatewiki.net
 *
 * @ingroup Language
 * @file
 *
 * @author Tagir
 */

$fallback = 'ru';

$messages = array(
# User preference toggles
'tog-underline'               => 'Ссылкаш белгалде:',
'tog-highlightbroken'         => 'Йоaцаж йоa ссылкаш <a href="" class="new">ер тайпа</a> хьокха (е ер тайпа<a href="" class="internal">?</a>).',
'tog-justify'                 => 'Параграф хьанийсъе',
'tog-hideminor'               => 'ЛоархIаме йоацаж йоа хувцамаш спискаех ма хьокха',
'tog-extendwatchlist'         => 'Со хьежачана спискаех лоархIаме йоалаж йоа хувцамаш хьокха',
'tog-usenewrc'                => 'Ериг йоалаж йоа керда хувцамаш (JavaScript)',
'tog-numberheadings'          => 'Кертера-деша таьрахь автоматически оттайе',
'tog-showtoolbar'             => 'Хувцамаш еж йоа панель инструментов хьокха (JavaScript)',
'tog-editondblclick'          => 'ОагIув хувца шозза клик йича (JavaScript)',
'tog-editsection'             => 'ХIара дакъа "хувца" ссылк хьахьокха',
'tog-editsectiononrightclick' => 'Дакъа хувца дакъа-цIерах аьтта клик йича (JavaScript)',
'tog-showtoc'                 => 'Оглавление хьокха (цу оагIувна кхьаннена дукхагIа дакъа йеле)',
'tog-rememberpassword'        => 'У компьютеретъ се цIи дагалаца',
'tog-editwidth'               => 'Хувцамаш еж йоа моттиг шера хьалийтта',
'tog-watchcreations'          => 'Аз хьаеж йоа оагIонаш со хьежача спискаех тIатоха',
'tog-watchdefault'            => 'Аз нийсъйеж йоа оагIонаш со хьежача спискаех тIатоха',
'tog-watchmoves'              => 'Аз цIи хийца оагIонаш со хьежача спискаех тIатоха',
'tog-watchdeletion'           => 'Аз дIаяькха оагIонаш со хьежача спискаех тIатоха',
'tog-minordefault'            => 'Ериг еж йоа хувцамаш лоархIаме йоацаж сен белгалде',
'tog-previewontop'            => 'Хувцамаш еж бIарахьажа хьалха',
'tog-previewonfirst'          => 'Эггара хьалха хувцамаш еж бIарахьажа хьалха',
'tog-nocache'                 => 'ОагIувна кеш е дехка',
'tog-enotifwatchlistpages'    => 'Э-майл хьадайта суна со хьежача оагIув хийцача',
'tog-enotifusertalkpages'     => 'Э-майл хьадайта суна аз къамял деж оагIув хийцача',
'tog-enotifminoredits'        => 'Э-майл хьадайта суна лоархIаме йоацаж йоа хувцамаш йиеча',
'tog-enotifrevealaddr'        => 'Хьокха са э-майл',
'tog-shownumberswatching'     => 'Хьокха масса сег хьежаш ба',
'tog-fancysig'                => 'Йоалача бесса кулг яздар (автоматически ссылк йоацаж)',
'tog-externaleditor'          => 'Ший йоала редактор харжа',
'tog-externaldiff'            => 'Диста де ший йоалла програм харжа',
'tog-showjumplinks'           => '"Дехьадала" ссылк хьахьокха',
'tog-uselivepreview'          => 'Сиха бIарахьажар (JavaScript) (Экспериментально)',
'tog-forceeditsummary'        => 'Хоам бе суна хувцамаш малагIа ер списка йеце',
'tog-watchlisthideown'        => 'Аз йа хувцамаш со хьежача спискаех ма хьокха',
'tog-watchlisthidebots'       => "Бот'ыз йа хувцамаш со хьежача спискаех ма хьокха",
'tog-watchlisthideminor'      => 'ЛоархIаме йоацаж йоа хувцамаш со хьежача спискаех ма хьокха',
'tog-ccmeonemails'            => 'Вуужаште аз дIадьахта э-майл суна а хьадайта',
'tog-diffonly'                => 'Диста къал йоалаж йоа оагIувна дакъа ма хьаокха',

'underline-always'  => 'Массаза',
'underline-never'   => 'ЦIаккха',
'underline-default' => 'Браузер настройкаш хьаэца',

'skinpreview' => '(Хьажа)',

# Dates
'sunday'        => 'КIиранди',
'monday'        => 'Оршот',
'tuesday'       => 'Шинара',
'wednesday'     => 'Кхаьра',
'thursday'      => 'Ера',
'friday'        => 'ПIаьраска',
'saturday'      => 'Шоатта',
'sun'           => 'КIи',
'mon'           => 'Орш',
'tue'           => 'Шин',
'wed'           => 'Кха',
'thu'           => 'Ера',
'fri'           => 'ПIаь',
'sat'           => 'Шоа',
'january'       => 'Январь',
'february'      => 'Февраль',
'march'         => 'Март',
'april'         => 'Апрель',
'may_long'      => 'Май',
'june'          => 'Июнь',
'july'          => 'Июль',
'august'        => 'Август',
'september'     => 'Сентябрь',
'october'       => 'Октябрь',
'november'      => 'Ноябрь',
'december'      => 'Декабрь',
'january-gen'   => 'Январь',
'february-gen'  => 'Февраль',
'march-gen'     => 'Март',
'april-gen'     => 'Апрель',
'may-gen'       => 'Май',
'june-gen'      => 'Июнь',
'july-gen'      => 'Июль',
'august-gen'    => 'Август',
'september-gen' => 'Сентябрь',
'october-gen'   => 'Октябрь',
'november-gen'  => 'Ноябрь',
'december-gen'  => 'Декабрь',
'jan'           => 'Янв',
'feb'           => 'Фев',
'mar'           => 'Мар',
'apr'           => 'Апр',
'may'           => 'Май',
'jun'           => 'Июн',
'jul'           => 'Июл',
'aug'           => 'Авг',
'sep'           => 'Сен',
'oct'           => 'Окт',
'nov'           => 'Ноя',
'dec'           => 'Дек',

# Categories related messages
'pagecategories'        => '{{PLURAL:$1|Категори|Категореш}}',
'category_header'       => '"$1" категори оагIонаш',
'subcategories'         => 'Чура-категореш',
'category-media-header' => '"$1" категори медиа',
'category-empty'        => "''Укх категори хьанзарчоа цхьаккха е оагIонаш, е медиа яц.''",

'about'          => 'Описани',
'article'        => 'ОагIув',
'newwindow'      => '(кердача курогIa хьаделла)',
'cancel'         => 'Юхавал',
'qbfind'         => 'Хьакораде',
'qbbrowse'       => 'Хьокха',
'qbedit'         => 'Хувца',
'qbpageoptions'  => 'Ер оагIув',
'qbpageinfo'     => 'Укхо чу фу да',
'qbmyoptions'    => 'Са оагIонаш',
'qbspecialpages' => 'ЛаьрххIа оагIувнаш',
'moredotdotdot'  => 'ДукхагIа ха...',
'mypage'         => 'Са оагIув',
'mytalk'         => 'Са къамаьл',
'anontalk'       => 'Цу IP ца къамаьл де',
'navigation'     => 'Навигаци',

'errorpagetitle'    => 'ГIалат',
'returnto'          => '$1 оагIувте юхавал',
'tagline'           => 'УкIхузер {{SITENAME}}',
'help'              => 'ГIо',
'search'            => 'Леха',
'searchbutton'      => 'Леха',
'go'                => 'Кхоачашде',
'searcharticle'     => 'Кхоачашде',
'history'           => 'У оагIувна хан хувцамаш',
'history_short'     => 'Хан хувцамаш',
'updatedmarker'     => 'Со ханача денца хувцамаш хьаний',
'info_short'        => 'Информаци',
'printableversion'  => 'Печати верси',
'permalink'         => 'Массаза йоалла ссылк',
'print'             => 'Печатать де',
'edit'              => 'Хувца',
'editthispage'      => 'Хувца ер оагIув',
'delete'            => 'Дiадаккха',
'deletethispage'    => 'Дiайаккха ер оагIув',
'undelete_short'    => 'Юхаметтаоттаде {{PLURAL:$1|oхувцам|$1 eхувцамаш}}',
'protect'           => 'Лораде',
'protect_change'    => 'Лорадер хувца',
'protectthispage'   => 'Лораде ер оагIув',
'unprotect'         => 'Ма лораде кхы',
'unprotectthispage' => 'Ма лораде кхы ер оагIув',
'newpage'           => 'Керда оагIув',
'talkpage'          => 'Ер оагIув йуца',
'talkpagelinktext'  => 'Къамьал',
'specialpage'       => 'ЛаьрххIа оагIув',
'personaltools'     => 'Се инструментыш',
'postcomment'       => 'Дош ала',
'articlepage'       => 'ОагIувнте хьажа',
'talk'              => 'Къамьал',
'views'             => 'Хьежараш',
'toolbox'           => 'Инструменташ',
'userpage'          => 'У сега оагIгув хьажа',
'projectpage'       => 'Ер проектаех оагIув хьажа',
'imagepage'         => 'У суртага оагIув хьажа',
'mediawikipage'     => 'У хоама оагIув хьажа',
'templatepage'      => 'Шаблона оагIув хьажа',
'viewhelppage'      => 'Гiо деха',
'categorypage'      => 'У категори оагIув хьажа',
'viewtalkpage'      => 'Къамьала хьажа',
'otherlanguages'    => 'Вокхо меттала',
'redirectedfrom'    => '($1 тIера хьайахьийта)',
'redirectpagesub'   => 'Йука меттиге йахьийта оагIув',
'lastmodifiedat'    => 'Тiехьара хувцам у оагIувна: $2, $1.', # $1 date, $2 time
'viewcount'         => 'Ер оагIув $1 хьо йай.',
'protectedpage'     => 'Лораеж йоа оагIув',
'jumpto'            => 'Уккуз дехьадала:',
'jumptonavigation'  => 'навигаци',
'jumptosearch'      => 'леха',

# All link text and link target definitions of links into project namespace that get used by other message strings, with the exception of user group pages (see grouppage) and the disambiguation template definition (see disambiguations).
'aboutsite'            => 'Описани {{SITENAME}}',
'aboutpage'            => 'Project:Описани',
'bugreports'           => 'Гiалата хоам',
'bugreportspage'       => 'Project:Гiалата хоам',
'currentevents'        => 'Хьанзар доалара хамаж',
'currentevents-url'    => 'Project:Хьанзар доалара хама',
'edithelp'             => 'Хувцамаш йие гIо',
'edithelppage'         => 'Help:Хувцамаш йие гIо',
'faq'                  => 'КХХ (Каста Хоатташ доа Хамаж)',
'faqpage'              => 'Project:КХХ (Каста Хоатташ доа Хамаж)',
'helppage'             => 'Help:Хьехар',
'mainpage'             => 'Кертера оагIув',
'mainpage-description' => 'Кертера оагIув',
'policy-url'           => 'Project:Бокъонаш',
'portal'               => 'Гiоз',
'portal-url'           => 'Project:ГIоз',
'privacy'              => 'Конфиденциальности бокъонаш',
'privacypage'          => 'Project:Конфиденциальности бокъона',

'badaccess'        => 'Чу валар гIалата',
'badaccess-group0' => 'Хьо де воалара хьюна де пурам дац',
'badaccess-group1' => 'Хьо де воалара $1 группе бол чар ма де йиша яц',
'badaccess-group2' => 'Хьо де воалара $1 группаш юкъе бол чар ма де йиша яц',
'badaccess-groups' => 'Хьо де воалара $1 группаш юкъе бол чар ма де йиша яц',

'versionrequired'     => '$1 MediaWiki верси йиза',
'versionrequiredtext' => '$1 MediaWiki верси йиза ер оагIув хьажа. [[Special:Version|version page]] хьажа.',

'ok' => 'ОК',

# Special:Categories
'categories' => 'Категореш',

);
