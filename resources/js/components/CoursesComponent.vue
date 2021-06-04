<template>
    <div>
        <div class="col-12">
            <div class="form-inline">
            <label for="">Course ID </label>
            <div class="input-group ml-2">
                <input type="text" class="form-control" v-model="selectedCourseId">
                <div class="input-group-append">
                    <a @click="validate" class="btn btn-success" href="#" role="button">Validate <i class="fas fa-check" v-if="success"></i></a>
                </div>
            </div>
            </div>

            <small id="helpId" class="form-text text-muted">Enter the Course ID for the Canvas site into which you would like to import a template. The Course ID is a six digit number that you will see at the end of the Canvas site URL in your browser's address bar.</small>
            <template v-if="value">
            <div class="mt-2 alert alert-danger" role="alert" v-if="value && value.workflow_state == 'available'">
                <strong>Warning:</strong> This course has already been published.
            </div>
                <p><strong>Course Designator:</strong> {{ value.course_code }} </p>
                <p><strong>Course Name:</strong> {{ value.name}} </p>
                
            </template>

            <!-- <img src="/images/canvas_course_id.png"> -->
        </div>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                selectedCourseId: this.value?this.value.id:null,
                success: false
            }
        },
        props: ["value"],
        methods: {
            validate: function () {
                axios.get("/api/canvas/validateCourse/" + this.selectedCourseId)
                    .then(res => {
                        if (res.data) {
                            this.$emit('input', res.data);
                            this.success = true;

                        }
                    })
                    .catch(err => {
                        alert("Invalid course ID, or you're not an teacher/designer for that course");
                    });
            }
        },
        mounted: function () {

        }
    }
</script>