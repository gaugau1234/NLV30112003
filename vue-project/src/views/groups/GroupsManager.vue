<script setup>
import Layout from '@/components/FrontEnd/Layout.vue'
import './GroupsManager.css'
import { GroupsManager } from './GroupsManager.js'

const {
    user, groups, groupMembers, selectedGroupId,
    showCreateModal, showRenameModal, newGroupName, renameGroupName, inviteEmail,
    addGroup, selectGroup, clearSelection, isGroupOwner,
    addMemberById, addMemberByEmail, removeMember,
    renameGroup, deleteGroup
} = GroupsManager();

</script>



<template>
    <layout>
        <template #main-content>
            <div class="group-management">
                <!-- Header -->
                <div class="header">
                    <h2>Danh sách nhóm</h2>
                    <button @click="showCreateModal = true">Tạo nhóm</button>

                    <!-- Modal tạo nhóm -->
                    <div v-if="showCreateModal" class="modal-overlay">
                        <div class="modal-content">
                            <h3>Tạo nhóm mới</h3>
                            <input v-model="newGroupName" type="text" placeholder="Tên nhóm..." />
                            <div class="modal-actions">
                                <button @click="addGroup">Tạo</button>
                                <button @click="showCreateModal = false">Hủy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách nhóm -->
                <div v-if="!selectedGroupId" class="group-list">
                    <ul>
                        <li v-for="group in groups" :key="group.id" @click="selectGroup(group.id)">
                            {{ group.name }} <span v-if="group.created_by === user?.id"></span>
                        </li>
                    </ul>
                </div>

                <!-- Chi tiết nhóm -->
                <div v-else class="member-list">
                    <h3>Thành viên</h3>
                    <p><strong>ID nhóm:</strong> {{ selectedGroupId }}</p>
                    <ul>
                        <template v-if="groupMembers[selectedGroupId] && groupMembers[selectedGroupId].length">
                            <li v-for="member in groupMembers[selectedGroupId]" :key="member.id">
                                {{ member.name }} ({{ member.email }})
                                <span v-if="member.pivot?.role === 'admin'" style="color: goldenrod;"> Admin</span>
                                <span v-else style="color: #666;"> Member</span>

                                <button v-if="isGroupOwner && member.id !== user?.id" @click="removeMember(member.id)"
                                    class="remove-btn">X</button>
                            </li>

                        </template>
                        <li v-else style="color: #888; font-style: italic;">Chưa có thành viên nào trong nhóm này.</li>
                    </ul>
                    <div class="member-actions">
                        <div v-if="isGroupOwner">
                            <button @click="showRenameModal = true"> Sửa tên nhóm</button>
                            <button @click="deleteGroup">Xoá nhóm</button>
                            <button @click="addMemberById">Thêm bằng ID</button>
                        </div>
                        <button @click="clearSelection">← Quay lại</button>
                    </div>

                    <!-- Modal sửa tên nhóm -->
                    <div v-if="showRenameModal" class="modal-overlay">
                        <div class="modal-content">
                            <h3>Đổi tên nhóm</h3>
                            <input v-model="renameGroupName" type="text" />
                            <div class="modal-actions">
                                <button @click="renameGroup">Lưu</button>
                                <button @click="showRenameModal = false">Hủy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </layout>
</template>
