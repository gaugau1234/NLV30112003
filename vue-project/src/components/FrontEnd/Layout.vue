<script setup>
import Sidebar from './Sidebar.vue';
import Chatbot from './Chatbot.vue';
// import Header from './Header.vue';

</script>
<template>
    <div class="layout-wrapper">
        <Sidebar />
        <div class="right-content">
            <!-- <Header /> -->
            <div class="main-content">
                <slot name="main-content">
                </slot>
                <router-view />
            </div>
        </div>
        <Chatbot />
    </div>
</template>
<style scoped>
.layout-wrapper {
    display: flex;
    min-height: 100vh;
    text-align: left;
    /* background-color: #f0f0f0; */
}

.right-content {
    margin-left: 160px;
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 100vh;
    /* background-color: #ffffff; */
    padding: 0;
}

.main-content {
    flex: 1;
    padding: 0;
    padding-right: 42px;
    display: block;
    border-radius: 16px;
    align-items: center;
    overflow-y: auto;
    /* background-color:#f5f5f5; */
}

/* Responsive: Nếu màn nhỏ thì sidebar thu gọn hoặc ẩn */
</style>
