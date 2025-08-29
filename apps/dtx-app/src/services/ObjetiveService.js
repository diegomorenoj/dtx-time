import axios from 'axios';
export default class ObjetiveService {
    all (filters) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/objetives`, {
            params: filters,
        }).then(res => {
            console.log(res);
            return res.data;
        });
    }

    getAllCourses (filterData) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetives/courseAll`, filterData);
    }

    getAllByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetives/filter`, filter).then(res => {
            return res.data;
        });
    }

    importExcel (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetives/objetivesExcelImport`, data).then(res => {
            return res.data;
        });
    }

    getAllByFilterGeneral (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetives/filtergeneral`, filter).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetives`, data).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/objetives/${id}`, data).then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/objetives/${id}`).then(res => {
            return res.data;
        });
    }
}
