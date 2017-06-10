var $tpl = $('#amz-tpl');
var source = $tpl.text();
var template = Handlebars.compile(source);
var data = {
	header: {
		"content": {
			"title": '成绩查询'
		}
	},
	navbar: {
		"options": {
			"cols": "4",
			"iconpos": "top"
		},
		"content": [{
				"title": "重置密码",
				"link": "#reset",
				"icon": "unlock-alt",
				"dataApi": ""
			},
			{
				"title": "查询成绩",
				"link": "#query",
				"icon": "search",
				"dataApi": ""
			},
			{

				"title": "关于此站",
				"link": "#coder",
				"icon": "gg",
				"dataApi": ""
			},
			{
				"title": "一键分享",
				"link": "",
				"icon": "share-square-o",
				"dataApi": "data-am-navbar-share"
			}
		]
	}
};
var html = template(data);
$tpl.before(html);