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
                    <div class=" mb-3">
                        <el-button type="primary" @click="handleDownload">
                            Download
                        </el-button>
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
                listSelection: [],
                loading: true,
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
                changeCurrentPage(val) {
                    this.fetchFiles(val);
                },
                handleSelectionChange(val) {
                    this.listSelection = val;
                },
                handleDownload() {
                    const len = this.listSelection.length;
                    if (len > 0) {
                        var formData = new FormData();
                        for (let i = 0; i < len; i++) {
                            formData.append('id[]', this.listSelection[i].id);
                        }
                        axios({
                            url: '{{ env('APP_URL_API') }}' + 'admin/files/download',
                            method: 'POST',
                            responseType: 'blob',
                            data: formData,
                        }).then((response) => {
                            const url = window.URL.createObjectURL(new Blob([response.data]));
                            const link = document.createElement('a');
                            link.href = url;
                            link.setAttribute('download', 'Documents.zip');
                            document.body.appendChild(link);
                            link.click();
                        });
                    } else {
                        this.$message('No files have been selected yet!');
                    }
                }
            },
            mounted() {
                this.fetchFiles(1);
            },
        });

    </script>
@endsection
