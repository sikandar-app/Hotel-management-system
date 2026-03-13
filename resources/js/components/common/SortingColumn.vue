<template>
    <i v-if="sortBy !== columnName" class="bx bx-sort-down"></i>
    <i v-else :class="sortDirection === 'asc' ? 'bx bx-sort-up' : 'bx bx-sort-down'"></i>
</template>
<script>
export default {
    props: {
        sortBy: {
            type: String,
            required: true,
        },
        sortDirection: {
            type: String,
            default: 'asc',
        },
        columnName: {
            type: String,
            required: true,
        },
    },
};
</script>