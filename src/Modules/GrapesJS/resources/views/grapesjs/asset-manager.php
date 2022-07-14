<div style="display: none">
    <div id="picker" class="">
        <div class="row mb-2 p-3 align-items-center justify-content-between">
            <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group mr-2" role="group" aria-label="Second group">
                    <button type="button" class="btn btn-light" @click="load()" title="Refresh"><i class="fa fa-refresh"></i></button>
                    <button @click="pickFile" type="button"  name="button" class="btn btn-light" :disabled="isUploading">
                        <span v-show="isUploading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span v-show="!isUploading"><i class="fa fa-upload"></i></span>
                    </button>
                </div>
                <div class="btn-group mr-2" role="group" aria-label="First group">
                    <button type="button" :disabled="isFirst" class="btn btn-light" @click="prev"><i class="fa fa-arrow-left"></i></button>
                    <button type="button" :disabled="!hasNext" class="btn btn-light" @click="next"><i class="fa fa-arrow-right"></i></button>
                </div>
            </div>
            <span v-if="imageData">Showing page {{ imageData.meta.current_page }} of {{ imageData.meta.last_page }}</span>

        </div>
        <div class="d-flex justify-content-between">

            <div class="input-group my-2">
                <input v-model="searchText" @keyup.prevent.enter="search" class="form-control" type="text"  placeholder="Search files...">
                <div class="input-group-append">
                    <button @click="search" type="button"  name="button" class="btn btn-primary" aria-label="Search posts"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <input @change="handleFileInput" type="file" multiple
                   class="d-none" ref="filepicker" accept=".jpg,.jpeg,.png" />
        </div>

        <div ref="container" class="" v-if="imageData" @dragenter.prevent="dragenter" >
            <div class="photos"  >
                <div @click="select(image.url)"  :key="image.id" v-for="image in imageData.data" class="parent">
                    <img class="img" :src="image.url "/>
                    <span class="file-info">{{ image.name }}</span>
                </div>
            </div>
        </div>
    </div>
</div>



<script type="text/javascript">



//window.editor.on('asset:remove', function(asset) {
//    let assetId = asset.attributes.public_id;
//    $.ajax({
//        type: "POST",
//        url: "<?//= phpb_url('pagebuilder', ['action' => 'upload_delete', 'page' => $page->getId()]) ?>//",
//        data: {
//            id: assetId
//        },
//        success: function() {
//        },
//        error: function() {
//        }
//    });
//});


// Vue image picker

window.vueApp = new Vue({
    el: '#picker',
    data: {
        imageData:null,
        files:[],
        form:null,
        searchText:"",
        isUploading: false
    },
    mounted() {

        this.load()
    },
    destroyed() {

    },
    methods: {
        select(p){
            const component = window.editor.getSelected();
            component.addAttributes({ image: p });
            window.editor.Modal.close()
        },

        handleAssets(props) {
            props.container.appendChild(this.$el);
            this.select = props.select;
            this.remove = props.remove;
        },

        load(url='/dashboard/sapi/media/all',){
            axios.get(url)
                .then(res=>{
                    this.imageData = res.data
                })
        },

        next(){
            this.load(this.imageData.links.next)
        },

        prev(){
            this.load(this.imageData.links.prev)
        },

        dragenter() {
            this.$refs.overlay.classList.remove('d-none')
            this.$refs.overlay.classList.add('drop-overlay')
        },

        dragleave() {
            this.$refs.overlay.classList.add('d-none')
            this.$refs.overlay.classList.remove('drop-overlay')
        },

        handleFileInput(e) {
            let formData = new FormData()
            let files = e.target.files
            if(!files) return;
            ([...files]).forEach(f => {
                this.files.push({ file: f, img: URL.createObjectURL(f)})
                formData.append('files[]',f)
            });

            this.form = formData
            this.upload()
        },

        handleFileDrop(e) {
            let formData = new FormData()
            let droppedFiles = e.dataTransfer.files
            // alert('dropped')
            if(!droppedFiles) return;
            ([...droppedFiles]).forEach(f => {
                this.files.push({ file: f, img: URL.createObjectURL(f)})
                formData.append('files[]',f)
            });

            this.form = formData
            this.upload()
        },

        upload(){
            this.isUploading = true
            axios.post('/dashboard/sapi/media/store', this.form)
                .then(res => {
                    toastr.sucess('Image uploaded !')
                    this.dragleave()
                })
                .catch(e =>{
                    toastr.error('Image upload failed !')
                })
                .finally(() =>{
                    this.isUploading = false
                    this.load()
                    this.clear()
                })
        },

        clear(){
            this.form = null
            this.files = []
            this.load()
        },

        pickFile(){
            this.$refs.filepicker.click()
        },

        search(){
            this.load('/dashboard/sapi/media/all?keyword=' + this.searchText)
        },

        setFile(e){
            this.$emit('done',e)
        },

        handleSelectedImage(e){
            this.$emit('selected',e)
            this.clear()
            this.$destroy()
        }


    },

    computed:{
        hasNext(){
            if(this.imageData){
                return this.imageData.links.next != null
            }
            return false
        },
        isFirst(){
            if(this.imageData){
                return this.imageData.links.prev == null
            }
            return true

        }
    }
});


window.editor.Commands.add('open-assets', {
    run(editor,sender, opts = {}) {
        const modal = editor.Modal;
        modal.setTitle('Image picker');
        modal.setContent(vueApp.$el)
        modal.open();
    }
})





</script>



<style>
    .photos {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-content: stretch;
        padding: 0;
        max-width: 980px;
        gap: 3px;
    }

    .photos img {
        display: block;
        float: left;
        flex: 0 0 auto;
        background-color: #fff;
        width: 200px;
        height: 200px;
        cursor: pointer;
        border-radius: 16px;
    }

    .parent {
        position: relative;
    }

    .parent:hover > .file-info {
        transform: scale(1);
    }

    .file-info {
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #00000080;
        color: #ffffff;
        border-radius: 0.5rem;
        padding: 8px;
        text-align: center;
        position: absolute;
        transform: scale(0);
        cursor: pointer;
    }

    }

</style>