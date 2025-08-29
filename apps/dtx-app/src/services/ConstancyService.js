import axios from 'axios';
export default class ConstancyService {
    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/constancies`, data).then(res => {
            console.log('CREATRE CONSTANCY', res.data);
            return res.data;
        });
    }

    update (data, id) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/constancies/${id}`, data).then(res => {
            console.log('UPDATE CONSTANCY', res.data);
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/constancies/${id}`)
        .then(res => {
            console.log('DELETE CONSTANCY', res.data);
            return res.data;
        });
    }
}
