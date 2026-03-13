<script>
import Layout from "../layouts/main.vue";
import Profile from "../components/widgets/profile.vue";
import Earning from "../components/widgets/earning.vue";
import Stat from "../components/widgets/stat.vue";
import Transaction from "../components/widgets/transaction.vue";
import Emailsent from "../components/widgets/emailsent.vue";

/**
 * Dashboard Component
 */
export default {
  components: { Layout, Profile, Stat, Transaction, Earning, Emailsent },
  data() {
    return {
      title: "Dashboard",
      showModal: false,
      statData: [
        {
          icon: "bx bx-copy-alt",
          title: "Orders",
          value: "1,235"
        },
        {
          icon: "bx bx-archive-in",
          title: "Revenue",
          value: "$35, 723"
        },
        {
          icon: "bx bx-purchase-tag-alt",
          title: "Average Price",
          value: "$16.2"
        }
      ],
      transactions: [
        {
          id: "#SK2540",
          name: "Neal Matthews",
          date: "07 Oct, 2019",
          total: "$400",
          status: "Paid",
          payment: ["fa-cc-mastercard", "Mastercard"],
          index: 1
        },
        {
          id: "#SK2541",
          name: "Jamal Burnett",
          date: "07 Oct, 2019",
          total: "$380",
          status: "Chargeback",
          payment: ["fa-cc-visa", "Visa"],
          index: 2
        },
        {
          id: "#SK2542",
          name: "Juan Mitchell",
          date: "06 Oct, 2019",
          total: "$384",
          status: "Paid",
          payment: ["fab fa-cc-paypal", "Paypal"],
          index: 3
        },
        {
          id: "#SK2543",
          name: "Barry Dick",
          date: "05 Oct, 2019",
          total: "$412",
          status: "Paid",
          payment: ["fa-cc-mastercard", "Mastercard"],
          index: 4
        },
        {
          id: "#SK2544",
          name: "Ronald Taylor",
          date: "04 Oct, 2019",
          total: "$404",
          status: "Refund",
          payment: ["fa-cc-visa", "Visa"],
          index: 5
        },
        {
          id: "#SK2545",
          name: "Jacob Hunter",
          date: "04 Oct, 2019",
          total: "$392",
          status: "Paid",
          payment: ["fab fa-cc-paypal", "Paypal"],
          index: 6
        }
      ]
    };
  },
   mounted() {
    setTimeout(() => {
      this.showModal = true;
    }, 1500);
  },
};
</script>

<template>
  <Layout>
    <!-- start page title -->
    <div class="row">
      <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
          <h4 class="mb-0 font-size-18">Dashboard</h4>

          <div class="page-title-right">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item active">Welcome to {{ appName }} Dashboard</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- end page title -->
  </Layout>
</template>
