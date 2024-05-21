
let ta = document.querySelector('#text')
CKSource.Editor
	.create(ta, {})
	.then(editor => {
		console.log('Editor was initialized', editor);
	}
	)
