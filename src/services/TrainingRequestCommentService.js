import axios from 'axios';
export default class TrainingRequestCommentService {
    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/comments`, data).then(res => {
            console.log('CREATRE COMMENT', res.data);
            return res.data;
        });
    }

    update (data, id) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/comments/${id}`, data).then(res => {
            console.log('UPDATE COMMENT', res.data);
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/comments/${id}`)
        .then(res => {
            console.log('CHANGE COMMENT', res.data);
            return res.data;
        });
    }
}
