import axios from 'axios';
export default class AuthService {
    login (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/users/login`, data).then(res => {
            console.log('LOGIN', res.data);
            return res.data;
        });
    }

    logout () {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/users/logout`, {}).then(res => {
            console.log('LOGOUT', res.data);
            return res.data;
        });
    }

    start () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/start`).then(res => {
            console.log('START', res.data);
            return res.data;
        });
    }
}
