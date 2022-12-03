
console.log( 'lalal')
let ta = document.querySelector( '#text' )
CKSource.Editor
			.create(ta  ,  {} )
			.then( editor => {
        		console.log( 'Editor was initialized', editor );
        		}
        	)


/*
CKEDITOR.replace('text', {
	language: 'de',
	toolbarGroups: [
		{ name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
		{ name: 'colors', groups: ['colors'] },
		{ name: 'links', groups: ['links'] },
		{ name: 'insert', groups: ['insert'] },
		{ name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'paragraph'] },
		{ name: 'clipboard', groups: ['clipboard', 'undo'] },
		{ name: 'tools', groups: ['tools'] },
		{ name: 'styles', groups: ['styles'] },
		{ name: 'editing', groups: ['find', 'selection', 'spellchecker', 'editing'] },
		{ name: 'document', groups: ['mode', 'document', 'doctools'] },
		{ name: 'others', groups: ['others'] }
	],
	removeButtons: 'Save,NewPage,Preview,Print,Templates,Form,Radio,Textarea,Select,Button,ImageButton,HiddenField,TextField,CreateDiv,BidiLtr,BidiRtl,Language,Find,Replace,SelectAll,Checkbox,Image,Table,PageBreak,Iframe,ShowBlocks,About,Anchor,Smiley',
	extraAllowedContent: 's',


});
*/






