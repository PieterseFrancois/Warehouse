import { Component, OnInit } from '@angular/core';
import { UserState } from './global/state/user-state';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss']
})

export class AppComponent implements OnInit {

    constructor( private user_state: UserState ) { }

    ngOnInit() {
        this.user_state.autoLogin();
    }

}