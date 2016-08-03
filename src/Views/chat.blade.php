<html>
<head>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.0/react.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/15.3.0/react-dom.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-core/5.8.34/browser.min.js"></script>
</head>
<body>
	 <div class="panel-body" id="container_chat" data-chat="{{$chat_id}}" data-user="{{$user->id}}" data-last="{{$last_id or 0}}">
	 <script type="text/babel" src="/chat.js"></script>
</body>
</html