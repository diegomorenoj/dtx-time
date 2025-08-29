import axios from 'axios';
export default class FileService {
    getAllById (id) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/trainings/${id}`).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/files`, data).then(res => {
            console.log('CREATRE TRAINING', res.data);
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/files/${id}`, data).then(res => {
            console.log('UPDATE TRAINING', res.data);
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/files/${id}`).then(res => {
            console.log('DELETE TRAINING', res.data);
            return res.data;
        });
    }
}
