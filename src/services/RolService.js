import axios from 'axios';
export default class RolService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/rols`).then(res => {
            console.log('DATA', res.data);
            return res.data;
        });
    }
}
