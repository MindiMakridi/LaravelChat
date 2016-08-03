    var user_id = $('#container_chat').data('user');
    var chat_id = $('#container_chat').data('chat');
    var last_message_id = $('#container_chat').data('last');

    if(!last_message_id) {
        last_message_id = 0;
    }

    var ChatList = React.createClass({
        getInitialState: function () {
            var messages = [];
            var last_id = last_message_id;
            var chat = chat_id;
            return {messages: messages, last_id: last_id, chat: chat, long: false};
        },
        componentDidMount: function () {
            if (this.state.chat) {
                this.long();
            }
        },
        initNewChat: function() {
            var messages = [];
            var last_id = 0;
            var chat = '';
            $.ajax({
                url: '/message/chat',
                async: false,
                data: {
                    id: React.findDOMNode(this.refs.react_new).value.trim()
                    },
                success: function (chat_hash) {
                    try {
                        chat = chat_hash;
                        this.setState({messages: messages, last_id: last_id, chat: chat});

                        if (this.state.chat) {
                          this.state.long.abort();
                        }

                        React.findDOMNode(this.refs.react_change).value = chat;
                        var self = this;
                        window.call_reload_counter();
                        setTimeout(function(){self.long()},1);
                    } catch (e) {
                        console.error('Error in query: ' + e);
                    }
                }.bind(this),
                error: function () {
                    this.error = true;
                    console.error('Not connection');
                }.bind(this)
            });
        },
        initChat: function(){
            var messages = [];
            var last_id = 0;
            var chat = React.findDOMNode(this.refs.react_change).value.trim();
            this.setState({messages: messages, last_id: last_id, chat: chat});

            if (this.state.chat) {
                this.state.long.abort();
            }

            var self = this;
            setTimeout(function(){self.long()},1);
        },
        long: function () {
            var self = this;
            var xhr = $.ajax({
                url: '/chat',
                method: 'post',
                async: true,
                data: {chat_id: this.state.chat, last_id: this.state.last_id},
                success: function (messages) {
                    try {
                        if (messages.length) {

                            var m = this.state.messages;
                            $.each(messages, function(index, msg){
                                m[msg.id] = msg;
                            });

                            this.setState({
                                last_id: messages[messages.length - 1].id,
                                messages: this.state.messages
                            });

                            $(document).trigger('scroll_bottom');
                        }
                        setTimeout(function(){self.long()}, 1000 * 3);
                    } catch (e) {
                        console.log('long - error: ', e);
                    }
                }.bind(this),
                error: function () {
                }.bind(this)
            });
            this.setState({long: xhr});
        },
        del:function(e, index){
            e.preventDefault();

            $.ajax({
                url: '/chat/destroy',
                async: true,
                method: 'post',
                data: {chat: this.state.chat, id: this.state.messages[index].id},
                success: function (data) {
                    try {
                        if(data){
                            var msg = this.state.messages;
                            msg.splice(index, 1);
                            this.setState({
                                messages: msg
                            });
                        }
                    } catch (e) {

                    }
                }.bind(this)
            });


        },
        sendMessage:function(e){

            var text = ReactDOM.findDOMNode(this.refs.text_msg).value.trim();

            if (this.state.chat.length)
            {
                $.ajax({
                    url: '/chat/send',
                    async: true,
                    type: 'post',
                    data: {chat: this.state.chat, text: text},
                    success: function (data) {
                        ReactDOM.findDOMNode(this.refs.text_msg).value = '';
                        this.long();
                    }.bind(this),
                    beforeSend: function(request) {
                        return request.setRequestHeader('X-CSRF-Token', $("meta[name='csrf-token']").attr('content'));
                    }
                });

                $(document).trigger('scroll_bottom');
            }

            e.preventDefault();
        },
        render: function () {

            var self = this;
            var str = '';

            var disabled = (this.state.chat) ? '' : 'disabled';

            if (this.state.messages.length == 0)
            {
                if (!this.state.chat) {
                    str = <i className="col-md-12 text-center no-msg">Выберите чат</i>
                } else {
                    str = <i className="col-md-12 text-center no-msg">Нет сообщений</i>
                }
            }

            return (
                <div>
                    <div className="b-message__body row">

                        { this.state.messages.map(function (msg, index) {
                            var status = msg.is_read == 1 ?  <span className="glyphicon glyphicon-ok glyph-ok"></span>: '';

                            if(msg.from_id != self.props.id_user){
                                status = '';
                                
                            } else {
                                var del = <a href="#" onClick={function(e){self.del(e,index)}}>Удалить</a>;
                            }
                            return (
                                <div key={msg.id} className="b-message__item clearfix col-md-12">
                                    <div className="chat-name">{msg.from.first_name}</div>
                                    <a href="#" className="user-name text-primary col-md-8">{msg.name_sender}</a>
                                    <div className="col-md-9 pull-left margin-top-10">
                                        {msg.text}
                                        <br/>
                                        {del}
                                    </div>
                                </div>
                            )
                        }) }

                        {str}
                    </div>

                    <form className="col-md-12 b-message__footer" onSubmit={self.sendMessage}>
                        <textarea disabled={disabled} className="form-control b-message__footer__field-text col-md-10" id="text_msg" ref="text_msg"/>
                        <button type="submit" className="btn btn-primary pull-right margin-top-10" disabled={disabled}>Отправить</button>
                        <input type="hidden" id="react_new" ref="react_new" onClick={self.initNewChat}/>
                        <input type="hidden" id="react_change" ref="react_change" onClick={self.initChat}/>
                    </form>
                </div>
            );
        }
    });
    ReactDOM.render(
            <ChatList id_user={user_id} />,
            document.getElementById('container_chat')
    );