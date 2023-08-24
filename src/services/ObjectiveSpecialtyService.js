import axios from 'axios';
export default class ObjectiveSpecialtyService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/objetivesspecialties`).then(res => {
            return res.data;
        });
    }

    getAllByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetivesspecialties/filter`, filter).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/objetivesspecialties`, data).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/objetivesspecialties/${id}`, data).then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/objetivesspecialties/${id}`).then(res => {
            return res.data;
        });
    }
}
