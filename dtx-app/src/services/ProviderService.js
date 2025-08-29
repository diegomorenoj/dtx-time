import axios from 'axios';
export default class ProviderService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/providers`).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/providers`, data).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/providers/${id}`, data).then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/providers/${id}`).then(res => {
            return res.data;
        });
    }
}
