<template>
    <select v-model="selectedCourseId" @change="$emit('input', selectedCourseId)" class="form-control">
        <option v-for="course in mergedCourse" :key="course.id" :value="course" :disabled="course.account_id == undefined">{{course.name}}</option>
    </select>
</template>

<script>
export default {
    data() {
        return {
            selectedCourseId: this.value,
            courses: [],
            terms: []
        }
    },
    props: ["value"],
    computed: {
        mergedCourse: function() {

            var outputArray = [];
            var currentTerm = null;
            
            for(const course of this.courses.filter(c => c.workflow_state == "unpublished")) {
                if(currentTerm != course.enrollment_term_id) {
                    var term = this.terms.filter(t => t.id == course.enrollment_term_id);
                    if(term.length > 0) {
                        outputArray.push(term[0]);
                    }
                    
                }
                currentTerm = course.enrollment_term_id;
                outputArray.push(course);
            }
            
            return outputArray;

        }
    },
    mounted: function() {
        axios.get("/api/canvas")
        .then((result) => { this.courses = result.data});
        axios.get("/api/terms")
        .then((result) => { this.terms = result.data});
    }
}
</script>