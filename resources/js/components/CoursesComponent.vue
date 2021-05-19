<template>
    <div>
        <div class="form-group col-12">
            <div class="form-inline">
            <label for="">Course ID </label>
            <div class="input-group ml-2">
                <input type="text" class="form-control" v-model="selectedCourseId">
                <div class="input-group-append">
                    <a @click="validate" class="btn btn-success" href="#" role="button">Validate <i class="fas fa-check" v-if="success"></i></a>
                </div>
            </div>
            </div>
            <p v-if="value" class="text-left">Course: {{ value.name}}</p>
            <small id="helpId" class="form-text text-muted">Enter the Course ID you'd like to apply a template to. This
                is a six digit number - you'll see at the end of the address in Canvas.</small>
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