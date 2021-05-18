@extends('index')

@section('content')
    <div id="content" v-loading="loading">
        <div>
            <el-row type="flex" justify="space-around">
                <el-col :span="16">
                    <el-table ref="multipleTable" :data="tableData" style="width: 100%" empty-text="Empty"
                        @selection-change="handleSelectionChange">
                        <el-table-column type="selection" width="55">
                        </el-table-column>
                        <el-table-column property="name" label="Name">
                        </el-table-column>
                    </el-table>
                    <br />
                    <div v-if="progressUpload" class="mb-3">
                        <el-progress :text-inside="true" :stroke-width="26" :percentage="percentageUpload">
                        </el-progress>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Multiple files input</label>
                        <input class="form-control" type="file" multiple @change="filesChange($event.target.files)">
                    </div>
                    <div class=" mb-3">
                        <el-button type="primary" round @click="handleUpload">
                            Upload
                        </el-button>
                        <el-popconfirm title="Are you sure?" confirm-button-text='OK' cancel-button-text='No, Thanks'
                            @confirm="handleAbort">
                            <el-button type="info" round slot="reference">
                                Abort
                            </el-button>
                        </el-popconfirm>
                        <el-popconfirm title="Are you sure to delete select?" confirm-button-text='OK'
                            cancel-button-text='No, Thanks' @confirm="handleDeleteSelect">
                            <el-button type="danger" round slot="reference">
                                Delete select
                            </el-button>
                        </el-popconfirm>
                        <el-popconfirm title="Are you sure to delete all?" confirm-button-text='OK'
                            cancel-button-text='No, Thanks' @confirm="handleDeleteAll">
                            <el-button type="danger" round slot="reference">
                                Delete all
                            </el-button>
                        </el-popconfirm>
                    </div>
                    <div class=" mb-3">
                        <el-pagination background layout="prev, pager, next" :page-count="pageCount"
                            :current-page="currentPage" @current-change="changeCurrentPage">
                        </el-pagination>
                    </div>
                </el-col>
            </el-row>
        </div>
    </div>
    <script>
        new Vue({
            el: '#content',
            data: {
                tableData: [],
                listUpload: [],
                listSelection: [],
                loading: true,
                progressUpload: false,
                percentageUpload: 0,
                pageCount: 1,
                currentPage: 1,
            },
            methods: {
                fetchFiles(page) {
                    this.loading = true;
                    const url = '{{ env('APP_URL_API') }}' + 'admin/files?page=' + page;
                    axios.get(url)
                        .then(response => {
                            this.tableData = response.data.data;
                            this.pageCount = response.data.meta.last_page;
                            this.currentPage = response.data.meta.current_page;
                        }).catch(error => {
                            this.$message.error('Error! An error occurred. Please try again later!');
                        }).finally(() => this.loading = false);
                },
                filesChange(fileList) {
                    if (fileList.length > 50) {
                        this.$message.warning('The maximum allowed number of uploads is 50!');
                    } else {
                        this.listUpload = fileList;
                    }
                },
                handleUpload() {
                    const len = this.listUpload.length;
                    if (len > 0) {
                        const url = '{{ env('APP_URL_API') }}' + 'admin/files';
                        this.progressUpload = true;
                        const percent = 100 / len;
                        let temp = 0;
                        for (let i = 0; i < len; i++) {
                            var formData = new FormData();
                            formData.append('file', this.listUpload[i]);
                            axios({
                                method: 'POST',
                                url: url,
                                data: formData,
                            }).finally(() => {
                                temp += percent;
                                this.percentageUpload = Math.round(temp);
                                if (i + 1 === len) {
                                    location.reload();
                                }
                            });
                        }
                    } else {
                        this.$message('No files have been selected yet!');
                    }
                },
                changeCurrentPage(val) {
                    this.fetchFiles(val);
                },
                handleAbort() {
                    location.reload();
                },
                handleSelectionChange(val) {
                    this.listSelection = val;
                },
                handleDeleteSelect() {
                    const len = this.listSelection.length;
                    if (len > 0) {
                        this.loading = true;
                        for (let i = 0; i < len; i++) {
                            const url = '{{ env('APP_URL_API') }}' + 'admin/files/' + this.listSelection[i].id;
                            axios.delete(url)
                                .finally(() => {
                                    this.listSelection.splice(i, 1);
                                    if (i + 1 === len) {
                                        this.$message.success('Delete success!');
                                        this.listSelection = [];
                                        this.fetchFiles(1);
                                    }
                                });
                        }

                    } else {
                        this.$message('No files have been selected yet!');
                    }
                },
                handleDeleteAll() {
                    this.loading = true;
                    const url = '{{ env('APP_URL_API') }}' + 'admin/files/all';
                    axios.delete(url)
                        .then(response => {
                            this.$message.success('Delete success!');
                        }).catch(error => {
                            this.$message.error('Error! An error occurred. Please try again later!');
                        }).finally(() => {
                            this.fetchFiles(1);
                        });
                }
            },
            mounted() {
                this.fetchFiles(1);
            },
        });

    </script>
@endsection
