<template>
    <div class="row p-3 checkbox_options">
        <div class="col-4" v-for="template in templates" :key="template.id">
            <div class=" p-0" >
                <input type="radio" :id="'template_'+template.id" name="template" v-model="selectedTemplate"
                            @change="$emit('input', selectedTemplate)" :value="template">
                <label :for="'template_'+template.id">
                <img class="card-img-top" :src="template.image_download_url">
                <div class="card-body">
                    
                    <h4 class="card-title">{{ template.name }}</h4>
                    
                    <p class="card-text"><a :href="'https://canvas.umn.edu/courses/' + template.id" target="_blank">Preview</a>
                    </p>
                            
                </div>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                templates: [],
                selectedTemplate: this.value
            }
        },
        props: ["value", "accountid"],
        mounted() {
            axios.get("/api/template?account_id=" + this.accountid)
                .then(res => {
                    this.templates = res.data.templates;
                });
        }
    }

</script>


<style scoped>

.checkbox_options {
    color: hsla(215, 5%, 50%, 1);
}
.checkbox_options input[type="radio"] {
  display: none;
}
.checkbox_options input[type="radio"]:not(:disabled) ~ label {
    cursor: pointer;
  }
.checkbox_options input[type="radio"]:disabled ~ label {
    color: hsla(150, 5%, 75%, 1);
    border-color: hsla(150, 5%, 75%, 1);
    box-shadow: none;
    cursor: not-allowed;
}
.checkbox_options  label {
  height: 100%;
  display: block;
  background: white;
  border: 2px solid hsla(150, 5%, 75%, 1);
  border-radius: 20px;
  padding: 1.3rem;
  margin-bottom: 1rem;
  text-align: left;
  box-shadow: 0px 3px 10px -2px hsla(150, 5%, 65%, 0.5);
  position: relative;
}
.checkbox_options  input[type="radio"]:checked + label {
  /* background:  #white; */
  /* color: hsla(215, 0%, 100%, 1); */
  box-shadow: 0px 0px 20px #17a2b8;
}
.checkbox_options  input[type="radio"]:checked + label::after {
    color: hsla(215, 5%, 25%, 1);
    border: 2px solid #17a2b8;
    content: "\f00c";
    font-weight: 900;
    font-family: "Font Awesome 5 Free";
    font-size: 24px;
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    height: 50px;
    width: 50px;
    line-height: 50px;
    text-align: center;
    border-radius: 50%;
    background: white;
    box-shadow: 0px 2px 5px -2px hsla(0, 0%, 0%, 0.25);

}

@media only screen and (max-width: 700px) {
  .checkbox_options  {
    flex-direction: column;
  }
}

.checkbox_options  {
  display: flex;
  flex-flow: row wrap;
}
.checkbox_options  > div {
  flex: 1;
  padding: 0.5rem;
}

</style>