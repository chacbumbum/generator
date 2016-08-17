<!DOCTYPE html>
<html lang="vn">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>SPA</title>
		<base href="/">
		<link rel="stylesheet" type="text/css" href="{!! asset('vendor/generate/css/vendor.css') !!}">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
		<div ui-view="header" class="header">

		</div>
		<div ui-view class="container"></div>

		<script src="{!! asset('vendor/generate/js/vendor.js') !!}"></script>
		<script src="{!! asset('vendor/generate/js/app.js') !!}"></script>
		<script src="{!! asset('vendor/generate/js/templates.js') !!}"></script>

	</body>
</html>