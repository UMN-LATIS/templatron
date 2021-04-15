<template>
    <select v-model="selectedCourseId" @change="$emit('input', selectedCourseId)" class="form-control">
        <option v-for="course in courses" :key="course.id" :value="course">{{course.name}}</option>
    </select>
</template>

<script>
export default {
    data() {
        return {
            selectedCourseId: this.value,
            courses: []
        }
    },
    props: ["value"],
    mounted: function() {
        axios.get("/api/canvas")
        .then((result) => { this.courses = result.data});
    }
}
</script>