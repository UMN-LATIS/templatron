<template>
    <div>

        <div class="row justify-content-center mt-0">
            <div class="col-11 col-sm-9 col-md-9 col-lg-8 p-0 mb-2">
                <div class="d-flex justify-content-center  text-center ">
                    <div class="ellipse">
                        <h1>Templatron</h1>
                    </div>
                </div>

                    
                <div class="card px-0 pt-4 pb-2 mt-3 mb-3">

                    <div class="row">
                        <div class="col-md-12 mx-0">
                            <ul id="progressbar" class="text-center">
                                <li v-bind:class="{active : stage==0}" id="course">
                                    <strong>Course</strong></li>
                                <li v-bind:class="{active : stage==1}" id="template">
                                    <strong>Template</strong></li>
                                <li v-bind:class="{active : stage==2}" id="confirm">
                                    <strong>Finish</strong></li>
                            </ul>

                            <div v-if="stage==0" class="">
                                <form class="form col-sm-8">
                                    <course-dropdown v-model="selectedCourse"></course-dropdown>
                                </form>
                                <div class="mt-2  text-center  ">
                                <!-- https://bbbootstrap.com/snippets/multi-step-form-wizard-30467045 -->
                                    <button @click="stage=1" class="btn btn-success" :disabled="selectedCourse == null">Next <i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                            <div v-if="stage==1">
                                <template-list v-model="template" :accountid="selectedCourse.map(c => c.account_id).filter(function (value, index, array) { 
return array.indexOf (value) == index;})"></template-list>
                               
                                <div class="mt-2  text-center ">
                                <!-- https://bbbootstrap.com/snippets/multi-step-form-wizard-30467045 -->
                                    <button @click="stage=0" class="btn btn-primary" ><i class="fas fa-arrow-left"></i> Back</button>
                                    <button @click="stage=2" class="btn btn-success" :disabled="template == null">Next <i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                            <div v-if="stage==2">
                                
                                <div class="col-sm-9 offset-sm-0">
                                <h2>Ready to go?</h2>
                                <div v-for="course in selectedCourse" :key="course.id">
                                    <p>Target Course: <strong> {{ course.name }}</strong></p>
                                    <p>Source Template: <strong> {{ template.name }}</strong></p>
                                </div>
                                <div class="form-check">
                                  <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" v-model="acknowledge" name="" id="" value="on">
                                    I acknowledge that importing this template will not delete or overwrite any existing content in my Canvas site and that it is best to import templates into an empty Canvas site.
                                  </label>
                                </div>
                                <div class="mt-2  text-center ">
                                <button @click="stage=1" class="btn btn-primary" ><i class="fas fa-arrow-left"></i> Back</button>
                                <button class="btn btn-success" @click="triggerImport" v-if="!submitted" :disabled="!acknowledge">Import!</button>

                                <div class="alert alert-success mt-2" role="alert"  v-if="submitted" v-for="course in selectedCourse" :key="course.id">
                                    <strong>Woohoo!</strong> Templatron is importing this template into your <a :href="'https://canvas.umn.edu/courses/' + course.id">Canvas site</a>. Please note that it may take a few minutes for the import process to complete.
                                </div>
                                
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>


<script>
    export default {
        data() {
            return {
                selectedCourse: null,
                stage: 0,
                template: null,
                submitted: false,
                acknowledge: false
            }
        },
        methods: {
            triggerImport: function() {
                axios.post("/api/canvas", {
                    selectedCourse: this.selectedCourse.map(c => c.id),
                    template: this.template.id
                })
                .then(response => {
                    if(response.data) {
                        this.submitted = true;
                    }
                });
            }
        }
    }

</script>

<style scoped>
    #progressbar {
        margin-bottom: 30px;
        overflow: hidden;
        color: lightgrey
    }

    #progressbar .active {
        color: #000000
    }

    #progressbar li {
        list-style-type: none;
        font-size: 12px;
        width: 33%;
        float: left;
        position: relative
    }

    #progressbar #course:before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f5da"
    }

    #progressbar #template:before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f0c5"
    }

    #progressbar #confirm:before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f09d"
    }


    #progressbar li:before {
        width: 50px;
        height: 50px;
        line-height: 45px;
        display: block;
        font-size: 18px;
        color: #ffffff;
        background: lightgray;
        border-radius: 50%;
        margin: 0 auto 10px auto;
        padding: 2px
    }

    #progressbar li:after {
        content: '';
        width: 100%;
        height: 2px;
        background: lightgray;
        position: absolute;
        left: 0;
        top: 25px;
        z-index: -1
    }

    #progressbar li.active:before,
    #progressbar li.active:after {
        background: skyblue
    }

    .card {
        z-index: 0
    }


.ellipse {
        /* position: absolute; */
        /* top: 50%; */
        /* left: 50%; */
        /* transform: translate(-50%, -50%); */
        height: 100px;
        width: 80%;
        /* left: 10%; */
        margin-bottom: -30px;
        border-radius: 0 0 500px 500px;
        background-color: skyblue;
        color: white;
        padding-top: 1.4em;
        font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif
    }
</style>