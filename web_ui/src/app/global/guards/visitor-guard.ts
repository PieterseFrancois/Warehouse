import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { UserState } from '../state/user-state';
import { User } from '../types';
import { routes } from 'src/app/app-routing.module';

@Injectable({
    providedIn: 'root'
})

export class VisitorGuard implements CanActivate {

    constructor( private user_state: UserState, private router: Router ) {}

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {

        var result: boolean = true;

        this.user_state.current_user$.subscribe(
            ( user: User ) => {

                result = user === undefined;
            }
        );
    
        if ( !result )
        {
            const profile_route: string = routes.find( element => element.title === 'Profile' ).path;
            this.router.navigateByUrl( profile_route ); 
        }

        return result;
    }
}
