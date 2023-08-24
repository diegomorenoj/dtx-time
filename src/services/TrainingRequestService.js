import axios from 'axios';
export default class TrainingRequestService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/trainings`).then(res => {
            return res.data;
        });
    }

    filter (val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/trainings/search/${val}`).then(res => {
            return res.data;
        });
    }

    getAllByUserId (userId) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/trainings/user/${userId}`).then(res => {
            return res.data;
        });
    }

    getAllByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/trainings/filter`, filter).then(res => {
            return res.data;
        });
    }

    getAllById (id) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/trainings/${id}`).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/trainings`, data).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/trainings/${id}`, data, { headers: { 'Content-Type': undefined } }).then(res => {
            return res.data;
        });
    }

    changeStatus (id, statusId) {
        const data = { status_id: statusId };
        return axios.put(`${process.env.VUE_APP_RUTA_API}/trainings/changestatus/${id}`, data)
        .then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/trainings/${id}`).then(res => {
            return res.data;
        });
    }
}
