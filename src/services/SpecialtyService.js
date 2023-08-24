import axios from 'axios';
export default class SpecialtyService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/specialties`).then(res => {
            return res.data;
        });
    }

    getAllByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/specialties/filter`, filter).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/specialties`, data).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/specialties/${id}`, data).then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/specialties/${id}`).then(res => {
            return res.data;
        });
    }

    storeExcel (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/specialties/excel/${id}`, data).then(res => {
            return res.data;
        });
    }
}
