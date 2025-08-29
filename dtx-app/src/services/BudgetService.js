import axios from 'axios';
export default class BudgetService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/budgets`).then(res => {
            return res.data;
        });
    }

    getMain () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/budgets/mainbudgets`).then(res => {
            return res.data;
        });
    }

    getAllByAnio (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/budgets/filter`, filter).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/budgets`, data).then(res => {
            return res.data;
        });
    }

    store (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/budgets/store`, data).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/budgets/${id}`, data).then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/budgets/${id}`).then(res => {
            return res.data;
        });
    }
}
