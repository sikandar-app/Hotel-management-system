<template>
    <flat-pickr 
        v-model="dateRange" 
        :config="datePickerConfig" 
        :placeholder="dateRangePlaceHolder"
        class="form-control" 
        @on-clear="clearDateRange"
    ></flat-pickr>
</template>
<script>
import flatPickr from 'vue-flatpickr-component';
import "flatpickr/dist/flatpickr.css";

export default {
    props: {
        placeHolder: {
            type: String,
            default: "Select Date Range",
        },
        datePickerConfig: {
            type: Object,
            default: () => ({
                mode: 'range',
                dateFormat: 'Y-m-d',
                defaultDate: [],
                onClose: null,
            }),
        }
    },
    components: {
        flatPickr,
    },
    watch:{
        dateRange(val){
            if (val && val.includes(' to ')) {
                const [startDateStr, endDateStr] = val.split(' to ');
                this.dates.start_date = startDateStr;
                this.dates.end_date = endDateStr;
                this.$emit('set-date', this.dates);
                this.updateUrlQuery(this.dates.start_date, this.dates.end_date);
            }
        }
    },
    data() {
        return {
            dateRange: [],
            dates: [],
        };
    },
    created() {
        if (this.$route.query?.start_date && this.$route.query?.end_date) {
            this.dateRange = [this.$route.query.start_date, this.$route.query.end_date];
            this.dates.start_date = this.$route.query.start_date;
            this.dates.end_date = this.$route.query.end_date;
            this.$emit('set-date', this.dates);
        }
    },
    computed: {
        dateRangePlaceHolder(){
            return this.placeHolder ?? "Select Date Range";
        }
    },
    methods:{
        onDateRangeClose(selectedDates) {
            if (!selectedDates.length) {
                this.clearDateRange();
            }
        },
        clearDateRange() {
            this.dateRange = [];
            this.$emit('set-date', this.dateRange);
            this.$router.replace({ query: { ...this.$route.query, start_date: undefined, end_date: undefined } });
        },
        updateUrlQuery(start_date, end_date) {
            this.$router.push({
                query: { ...this.$route.query, start_date, end_date }
            });
        }
    }
}
</script>