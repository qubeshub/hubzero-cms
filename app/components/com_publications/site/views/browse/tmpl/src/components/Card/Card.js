import React, { useState } from 'react'
import Tag from '../Icons/Tag'
import ArrowRight from '../Icons/ArrowRight'
import Download from '../Icons/Download'
import Adapt from '../Icons/Adapt'
import Comments from '../Icons/Comments'
import Watch from '../Icons/Watch'
import Calendar from '../Icons/Calendar'

import './Card.scss'

const Card = (props) => {
    const [active, setActive] = useState(false);

    const backgroundImg = 'https://192.168.33.10/publications/7/7?media=Image:master';

    const handleClick = (event) => {
        const card = event.target.closest('.card')
        const icon = card.querySelector('i')
        const links = card.querySelector('.sub-menu a')
        const content = card.querySelector('.card-content')

        icon.classList.add('icon-spinner')

        if (card.classList.contains('active')) {

            setActive(false)
            
            card.classList.remove('active')
            links.setAttribute('aria-hidden', 'true')
            links.setAttribute('tabindex', '-1')
            content.setAttribute('tabindex', '-1')

            window.setTimeout(function () {
                icon.classList.remove('arrow-left')
                icon.classList.remove('icon-spinner')
                icon.classList.add('menu')
            }, 800);
        } else {

            setActive(true)

            card.classList.add('active')
            links.setAttribute('aria-hidden', 'false')
            links.setAttribute('tabindex', '0')
            content.setAttribute('tabindex', '0')
            content.focus()
        
            window.setTimeout(function () {
                icon.classList.remove('menu')
                icon.classList.remove('icon-spinner')
                icon.classList.add('arrow-left')
            }, 800);
        }
    }

    return (
        <div className='card' style={{ backgroundImage: `url(${props.pubInfo.thumbUrl})` }}>
            <div className='card-content' tabIndex='-1'>
                <div className='title'>
                    <h3>
                        <a href={props.pubInfo.uri}>
                            {props.pubInfo.title}
                        </a>
                    </h3>
                    <p className='authors' title='CMS Manager' >{props.pubInfo.authors[0].name}</p>
                    <p className='hist'>
                        <span className = 'versions' >{props.pubInfo.versionLabel}</span>
                    </p> 
                </div>
                
                <div className='abstract'>{props.pubInfo.abstract}</div>
                
                <div className='sub-menu'>
                    <a
                        aria-label='Full Record'
                        title='Full Record'
                        href={props.pubInfo.uri}
                        aria-hidden='true'
                        tabIndex='-1'
                    >
                        <span className='menu-icon'>
                            <ArrowRight />
                        </span>
                        Full Record
                    </a>

                    <a
                        aria-label='Download'
                        title='Download'
                        href={`${props.pubInfo.uri}/serve/1?/render=archive`}
                        aria-hidden='false'
                        tabIndex='0'
                    >
                        <span className='menu-icon'>
                            <Download />
                        </span>
                    </a>

                    <a
                        aria-label='Adapt'
                        title='Adapt'
                        href={`${props.pubInfo.uri}/forks/1?action=fork`}
                        aria-hidden='false'
                        tabIndex='0'
                    >
                        <span className = 'menu-icon' >
                            <Adapt />
                        </span>
                    </a>

                    <a
                        aria-label='Comments'
                        title = 'Comments'
                        href = {`${props.pubInfo.uri}/comments/1#commentform`}
                        aria-hidden='false'
                        tabIndex='0'
                    >
                        <span className = 'menu-icon' >
                            <Comments />
                        </span> 
                    </a>

                    <a
                        aria-label='Watch'
                        title='Click to receive notifications when a new version is released'
                        href={`${props.pubInfo.uri}/watch/1?confirm=1&action=subscribe`}
                        aria-hidden ='false'
                        tabIndex='0' >
                        <span className='menu-icon'>
                            <Watch />
                        </span>  
                    </a>
                </div>
            </div>

            <button
                aria-label='More Information'
                title='More Information'
                href='#'
                className='btn-action'
                onClick={handleClick}
            >
                <i className='menu'></i>
            </button>

            <div className='meta'>
                <div
                    className='tag-wrap'
                    aria-label='Tags'
                    title='naps, cats, hoomins, meowing, napping, foods, pawing, snuggles, carried, personal space, understanding, property issues, affection'
                >
                    <span className='icons'>
                        <Tag />
                    </span>
                    <span>
                    <span className="tags"> naps, </span>
                    <span className="tags">cats, </span >
                    <span className="tags"> hoomins, </span>
                    <span className="tags">meowing, </span >
                    <span className="tags"> napping, </span>
                    <span className="tags">foods, </span >
                    <span className="tags"> pawing, </span>
                    <span className="tags">snuggles, </span>
                    <span className="tags"> carried, </span>
                    <span className="tags">personal space, </span >
                    <span className="tags">understanding, </span>
                    <span className="tags">property issues, </span >
                    <span className="tags" > affection </span>
                    </span>
                </div>

                <div className='views'>
                    <span aria-label='Views' title='Views'>
                    <span className='icons'>
                        <Watch />
                    </span>
                        4
                    </span>
                </div>

                <div className='downloads'>
                    <span aria-label='downloads' title='downloads'>
                    <span className = 'icons'>
                        <Download />
                    </span>
                        1
                    </span>  
                </div>

                <div className='forks'>
                    <span aria-label='Adaptations' title='Adaptations'>
                    <span className='icons'>
                        <Adapt /> 
                    </span>
                        0
                    </span>   
                </div>

                <div className='comments'>
                    <span aria-label='comments' title='comments'>
                    <span className='icons'>
                        <Comments /> 
                    </span>
                        0
                    </span>   
                </div>

                <div className='date'>
                    <span aria-label='Publish Date' title = 'Publish Date'>
                    <span className='icons'>
                        <Calendar /> 
                    </span>
                        02.2020
                    </span>   
                </div>
            </div>
        </div>
    )
}

export default Card