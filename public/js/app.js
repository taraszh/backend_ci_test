var app = new Vue({
	el: '#app',
	data: {
		login: '',
		pass: '',
		post: false,
		invalidLogin: false,
		invalidPass: false,
		invalidSum: false,
		posts: [],
		addSum: 0,
		amount: 0,
		likes: 0,
		commentText: '',
		packs: [
			{
				id: 1,
				price: 5
			},
			{
				id: 2,
				price: 20
			},
			{
				id: 3,
				price: 50
			},
		],
	},
	computed: {
		test: function () {
			var data = [];
			return data;
		}
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})
	},
	methods: {
		logout: function () {
			console.log ('logout');
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false
				axios.post('/main_page/login', {
					login: self.login,
					password: self.pass
				})
					.then(function (response) {
						console.log(response);
						if (response.data.status === 'error') {
							alert(response.data.error_message)
							self.pass = ''
						} else {
							$('#loginModal').modal('hide');
							setTimeout(function () {
								location.reload();
							}, 500);
						}
					});
			}
		},
		fiilIn: function () {
			var self= this;
			if(self.addSum === 0){
				self.invalidSum = true
			}
			else{
				self.invalidSum = false
				axios.post('/main_page/add_money', {
					sum: self.addSum,
				})
					.then(function (response) {
						if (response.data.status === 'error') {
							alert(response.data.error_message)
						} else {
							alert("Total balance: " + response.data.amount)
							setTimeout(function () {
								$('#addModal').modal('hide');
							}, 500);
						}
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (id, comment_id = null) {
			var self = this;
			axios
				.post('/main_page/like', {
						'id': id,
						'comment_id': comment_id,
				})
				.then(function (response) {
					if (response.data.status === 'error') {
						alert(response.data.error_message)
					} else {
						self.post = response.data.post;
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})

		},
		buyPack: function (id) {
			var self= this;
			axios.post('/main_page/buy_boosterpack', {
				id: id,
			})
				.then(function (response) {
					if (response.data.status === 'error') {
						alert(response.data.error_message)
					} else {
						self.amount = response.data.amount
						if(self.amount !== 0){
							setTimeout(function () {
								$('#amountModal').modal('show');
							}, 500);
						}
					}
				})
		},
		addComment: function (post_id) {
			let self = this;
			if(self.commentText !== '') {
				axios.post('/main_page/comment', {
					'post_id': post_id,
					'text': self.commentText
				})
					.then(function (response) {
						if (response.data.status === 'error') {
							alert(response.data.error_message)
						} else {
							self.commentText = '';
							self.openPost(post_id);
						}
					});
			} else {
				alert("Comment text cannot be empty")
			}
		}
	}
});

