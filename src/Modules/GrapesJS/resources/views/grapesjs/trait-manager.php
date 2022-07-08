<script type="text/javascript">

editor.TraitManager.addType('image', {
    createInput({ trait }) {
        const traitName = trait.attributes.name;
        const el = document.createElement('div');
        el.innerHTML = `
<div class="image__preview-container">
    <img class="image__preview cursor-pointer" src="<?= phpb_asset('pagebuilder/images/image-placeholder.png') ?>">
</div>
        `;
        const preview = el.querySelector('.image__preview');

        const component = window.editor.getSelected();
        if (component.attributes.attributes[traitName] !== undefined && component.attributes.attributes[traitName] !== '') {
            preview.setAttribute('src', component.attributes.attributes[traitName]);
        }

        preview.addEventListener('click', event => {
            window.editor.runCommand('open-assets', {
                onSelect: asset => {
                    let testImage  = "https://media.istockphoto.com/photos/mountain-landscape-picture-id517188688?k=20&m=517188688&s=612x612&w=0&h=i38qBm2P-6V4vZVEaMy_TaTEaoCMkYhvLCysE7yJQ5Q="
                    // preview.setAttribute('src', asset.attributes.src);
                    preview.setAttribute('src', testImage);
                    // update component setting and trigger editor component:update event
                    component.addAttributes({[traitName]: testImage});
                    // component.addAttributes({[traitName]: asset.attributes.src});

                }
            });
        });

        return el;
    }
});

</script>
